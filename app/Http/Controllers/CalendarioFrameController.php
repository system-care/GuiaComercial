<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsAppConfirmationJob;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use App\Services\RecurringAppointmentService;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarioFrameController extends Controller
{
    public function frame()
    {
        return view('calendario-frame', [
            'csrfToken'      => csrf_token(),
            'eventsUrl'      => route('calendario.events'),
            'statusUrl'      => route('calendario.status'),
            'rescheduleUrl'  => route('calendario.reschedule'),
            'optionsUrl'     => route('calendario.options'),
            'storeUrl'       => route('calendario.store'),
            'quickCustomerUrl' => route('calendario.quick-customer'),
            'createUrl'      => route('filament.admin.resources.appointments.create'),
        ]);
    }

    public function options(): JsonResponse
    {
        $user = auth()->user();

        $customersQ = Customer::query()->where('active', true)->orderBy('name');
        $servicesQ  = Service::query()->where('active', true)->orderBy('name');
        $profsQ     = Professional::query()->where('active', true)->orderBy('name');

        if (! $user->isSuperAdmin()) {
            $customersQ->where('tenant_id', $user->tenant_id);
            $servicesQ->where('tenant_id', $user->tenant_id);
            $profsQ->where('tenant_id', $user->tenant_id);
        }

        return response()->json([
            'customers'     => $customersQ->get(['id', 'name', 'phone', 'document'])->toArray(),
            'services'      => $servicesQ->get(['id', 'name', 'duration_minutes'])->toArray(),
            'professionals' => $profsQ->get(['id', 'name', 'specialty', 'color'])->toArray(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id'     => 'required|integer',
            'service_id'      => 'nullable|integer',
            'professional_id' => 'required|integer',
            'date'            => 'required|date_format:Y-m-d',
            'start_time'      => 'required|date_format:H:i',
            'end_time'        => 'required|date_format:H:i|after:start_time',
            'status'          => 'required|string',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $tenantId = $user->isSuperAdmin()
            ? Customer::withoutGlobalScope('tenant')->find($request->customer_id)?->tenant_id
            : $user->tenant_id;

        // Prevent IDOR: ensure related entities belong to the authenticated user's tenant.
        if (! $user->isSuperAdmin()) {
            $belongsToTenant = fn ($model, $id) => $id
                ? $model::withoutGlobalScope('tenant')->where('id', $id)->where('tenant_id', $tenantId)->exists()
                : true;

            if (! $belongsToTenant(Customer::class, $request->customer_id)) {
                return response()->json(['error' => 'Cliente não pertence à sua empresa.'], 403);
            }
            if (! $belongsToTenant(Service::class, $request->service_id)) {
                return response()->json(['error' => 'Serviço não pertence à sua empresa.'], 403);
            }
            if (! $belongsToTenant(Professional::class, $request->professional_id)) {
                return response()->json(['error' => 'Profissional não pertence à sua empresa.'], 403);
            }
        }

        $appointment = Appointment::create([
            'tenant_id'       => $tenantId,
            'customer_id'     => $request->customer_id,
            'service_id'      => $request->service_id,
            'professional_id' => $request->professional_id,
            'date'            => $request->date,
            'start_time'      => $request->start_time,
            'end_time'        => $request->end_time,
            'status'          => $request->status,
            'notes'           => $request->notes,
        ]);

        $appointment->load(['customer', 'service', 'professional']);

        SendWhatsAppConfirmationJob::dispatch($appointment->id)->delay(5);

        return response()->json(['ok' => true, 'event' => $this->toEvent($appointment)]);
    }

    public function quickCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        $user = auth()->user();
        $tenantId = $user->isSuperAdmin() ? null : $user->tenant_id;

        if (! $tenantId) {
            return response()->json(['error' => 'Tenant não identificado'], 422);
        }

        $customer = Customer::create([
            'tenant_id' => $tenantId,
            'name'      => $request->name,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'active'    => true,
        ]);

        return response()->json(['ok' => true, 'customer' => ['id' => $customer->id, 'name' => $customer->name, 'phone' => $customer->phone]]);
    }

    public function events(Request $request): JsonResponse
    {
        $user = auth()->user();
        $from = Carbon::parse($request->start)->format('Y-m-d');
        $to   = Carbon::parse($request->end)->format('Y-m-d');

        $query = Appointment::with(['customer', 'service', 'professional'])
            ->whereBetween('date', [$from, $to]);

        if (! $user->isSuperAdmin()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        if ($user->isProfissional() && ! $user->hasPermission(User::PERM_VER_AGENDA_GERAL)) {
            $query->where('professional_id', $user->professional_id);
        }

        return response()->json(
            $query->get()->map(fn ($a) => $this->toEvent($a))
        );
    }

    public function changeStatus(Request $request): JsonResponse
    {
        $appointment = Appointment::findOrFail($request->id);
        $user        = auth()->user();

        if (! $user->isSuperAdmin() && $appointment->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $appointment->update(['status' => $request->status]);

        return response()->json(['ok' => true, 'event' => $this->toEvent($appointment->fresh(['customer','service','professional']))]);
    }

    public function reschedule(Request $request): JsonResponse
    {
        $request->validate([
            'id'    => 'required|integer',
            'start' => 'required|string',
            'end'   => 'required|string',
        ]);

        $appointment = Appointment::findOrFail($request->id);
        $user        = auth()->user();

        if (! $user->isSuperAdmin() && $appointment->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $start = Carbon::parse($request->start);
        $end   = Carbon::parse($request->end);

        $customData = array_merge($appointment->custom_data ?? [], [
            'rescheduled_at' => now()->toISOString(),
        ]);

        $appointment->update([
            'date'                => $start->format('Y-m-d'),
            'start_time'          => $start->format('H:i'),
            'end_time'            => $end->format('H:i'),
            'confirmation_status' => 'pending',
            'custom_data'         => $customData,
        ]);

        return response()->json(['ok' => true, 'date' => $start->format('Y-m-d'), 'start' => $start->format('H:i'), 'end' => $end->format('H:i')]);
    }

    public function createRecurring(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id'          => 'required|integer',
            'service_id'           => 'nullable|integer',
            'professional_id'      => 'required|integer',
            'date'                 => 'required|date_format:Y-m-d',
            'start_time'           => 'required|date_format:H:i',
            'end_time'             => 'required|date_format:H:i|after:start_time',
            'status'               => 'required|string',
            'recurrence_mode'      => 'required|in:count,end_date',
            'recurrence_count'     => 'required_if:recurrence_mode,count|nullable|integer|min:2|max:52',
            'recurrence_end_date'  => 'required_if:recurrence_mode,end_date|nullable|date|after:date',
        ]);

        $user     = auth()->user();
        $tenantId = $user->isSuperAdmin()
            ? Customer::withoutGlobalScope('tenant')->find($request->customer_id)?->tenant_id
            : $user->tenant_id;

        if (! $user->isSuperAdmin()) {
            $hasPlan = Subscription::where('tenant_id', $tenantId)
                ->where('status', Subscription::STATUS_ACTIVE)
                ->exists();
            if (! $hasPlan) {
                return response()->json(['error' => 'Agendamento recorrente disponível apenas nos planos pagos.'], 403);
            }

            $belongsToTenant = fn ($model, $id) => $id
                ? $model::withoutGlobalScope('tenant')->where('id', $id)->where('tenant_id', $tenantId)->exists()
                : true;

            if (! $belongsToTenant(Customer::class, $request->customer_id)) {
                return response()->json(['error' => 'Cliente não pertence à sua empresa.'], 403);
            }
            if (! $belongsToTenant(Professional::class, $request->professional_id)) {
                return response()->json(['error' => 'Profissional não pertence à sua empresa.'], 403);
            }
        }

        $result = app(RecurringAppointmentService::class)->createSeries($request->all(), $tenantId);

        $msg = count($result['created']) . ' agendamento(s) criado(s)';
        if (! empty($result['skipped'])) {
            $msg .= '. Datas ignoradas por conflito: ' . implode(', ', $result['skipped']);
        }

        return response()->json(['ok' => true, 'message' => $msg, 'created' => count($result['created']), 'skipped' => $result['skipped']]);
    }

    public function cancelSeries(Request $request): JsonResponse
    {
        $request->validate([
            'appointment_id' => 'required|integer',
            'scope'          => 'required|in:this,future',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        $user        = auth()->user();

        if (! $user->isSuperAdmin() && $appointment->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        if (! $appointment->recurrence_group_id) {
            return response()->json(['error' => 'Este agendamento não faz parte de uma série.'], 422);
        }

        $query = Appointment::inGroup($appointment->recurrence_group_id)
            ->whereNotIn('status', ['completed', 'cancelled']);

        if ($request->scope === 'future') {
            $query->where('date', '>=', $appointment->date->format('Y-m-d'));
        } else {
            $query->where('id', $appointment->id);
        }

        $count = 0;
        $query->each(function ($a) use (&$count) {
            $a->update(['status' => 'cancelled']);
            $count++;
        });

        return response()->json(['ok' => true, 'cancelled' => $count]);
    }

    public function waLink(Request $request): JsonResponse
    {
        $request->validate(['appointment_id' => 'required|integer']);

        $appointment = Appointment::with(['tenant', 'customer', 'service'])->findOrFail($request->appointment_id);
        $user        = auth()->user();

        if (! $user->isSuperAdmin() && $appointment->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $phone = preg_replace('/\D/', '', $appointment->customer->phone ?? '');
        if (! $phone) {
            return response()->json(['error' => 'Cliente n\u{00E3}o possui telefone cadastrado.'], 422);
        }

        if (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }

        if (! $appointment->confirmation_token) {
            $appointment->update(['confirmation_token' => \Illuminate\Support\Str::random(48)]);
            $appointment->refresh();
        }

        $confirmUrl = route('appointment.confirm', ['token' => $appointment->confirmation_token]);
        $settings   = \App\Models\TenantSetting::where('tenant_id', $appointment->tenant_id)->value('settings') ?? [];
        $template   = $settings['confirmation_message'] ?? null;

        $text = app(\App\Services\WhatsAppNotificationService::class)
            ->buildBody($appointment, $confirmUrl, $template ?: null);

        $url = 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);

        return response()->json(['ok' => true, 'url' => $url]);
    }

    private function toEvent(Appointment $a): array
    {
        static $colors = [
            'pending'    => '#f59e0b',
            'confirmed'  => '#3b82f6',
            'arrived'    => '#8b5cf6',
            'in_service' => '#f97316',
            'completed'  => '#10b981',
            'cancelled'  => '#ef4444',
            'no_show'    => '#6b7280',
        ];
        static $labels = [
            'pending'    => 'Agendado',
            'confirmed'  => 'Confirmado',
            'arrived'    => 'Chegou',
            'in_service' => 'Em atendimento',
            'completed'  => 'Concluído',
            'cancelled'  => 'Cancelado',
            'no_show'    => 'Não compareceu',
        ];

        $bg = $colors[$a->status] ?? '#6b7280';

        return [
            'id'              => (string) $a->id,
            'title'           => ($a->customer->name ?? '?') . "\n" . ($a->service->name ?? '?'),
            'start'           => $a->date->format('Y-m-d') . 'T' . substr($a->start_time, 0, 5),
            'end'             => $a->date->format('Y-m-d') . 'T' . substr($a->end_time, 0, 5),
            'backgroundColor' => $bg,
            'borderColor'     => $bg,
            'textColor'       => '#ffffff',
            'extendedProps'   => [
                'customer'      => $a->customer->name     ?? '—',
                'service'       => $a->service->name      ?? '—',
                'professional'  => $a->professional->name ?? '—',
                'status'        => $a->status,
                'status_label'  => $labels[$a->status] ?? $a->status,
                'status_color'  => $bg,
                'notes'         => $a->notes ?? '',
                'start_time'    => substr($a->start_time, 0, 5),
                'end_time'      => substr($a->end_time, 0, 5),
                'date_fmt'      => $a->date->format('d/m/Y'),
                'edit_url'      => route('filament.admin.resources.appointments.edit', $a->id),
                'customer_phone'       => preg_replace('/\D/', '', $a->customer->phone ?? ''),
                'recurrence_group_id'  => $a->recurrence_group_id,
                'recurrence_index'     => $a->recurrence_index,
            ],
        ];
    }
}
