<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Subscription;
use App\Models\User;
use App\Support\Permission;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Calendario extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'Calendário';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = '';

    public function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    protected string $view = 'filament.pages.calendario';

    protected function getViewData(): array
    {
        $user = auth()->user();
        $isPaidPlan = ! $user->isSuperAdmin() && Subscription::where('tenant_id', $user->tenant_id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->exists();

        return ['isPaidPlan' => $isPaidPlan];
    }

    public static function canAccess(): bool
    {
        return Permission::isTenantMember();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getEvents(string $start, string $end): array
    {
        $user  = auth()->user();
        $from  = Carbon::parse($start)->format('Y-m-d');
        $to    = Carbon::parse($end)->format('Y-m-d');

        $query = Appointment::with(['customer', 'service', 'professional'])
            ->whereBetween('date', [$from, $to]);

        if (! $user->isSuperAdmin()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        if ($user->isProfissional() && ! $user->hasPermission(User::PERM_VER_AGENDA_GERAL)) {
            $query->where('professional_id', $user->professional_id);
        }

        return $query->get()->map(function (Appointment $a) {
            $bg = $this->statusHexColor($a->status);

            return [
                'id'              => (string) $a->id,
                'title'           => ($a->customer->name ?? '?') . "\n" . ($a->service->name ?? '?'),
                'start'           => $a->date->format('Y-m-d') . 'T' . substr($a->start_time, 0, 5),
                'end'             => $a->date->format('Y-m-d') . 'T' . substr($a->end_time, 0, 5),
                'backgroundColor' => $bg,
                'borderColor'     => $bg,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'customer'       => $a->customer->name     ?? '—',
                    'service'        => $a->service->name      ?? '—',
                    'professional'   => $a->professional->name ?? '—',
                    'status'         => $a->status,
                    'status_label'   => $this->statusLabel($a->status),
                    'status_color'   => $this->statusHexColor($a->status),
                    'notes'          => $a->notes ?? '',
                    'start_time'     => substr($a->start_time, 0, 5),
                    'end_time'       => substr($a->end_time, 0, 5),
                    'date_fmt'       => $a->date->format('d/m/Y'),
                    'edit_url'       => route('filament.admin.resources.appointments.edit', $a->id),
                    'customer_phone' => preg_replace('/\D/', '', $a->customer->phone ?? ''),
                ],
            ];
        })->toArray();
    }

    public function changeStatus(int $id, string $status): void
    {
        $appointment = Appointment::findOrFail($id);
        $user        = auth()->user();

        if (! $user->isSuperAdmin() && $appointment->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $appointment->update(['status' => $status]);

        Notification::make()
            ->title($this->statusLabel($status))
            ->success()
            ->send();
    }

    public function reschedule(int $id, string $newStart, string $newEnd): void
    {
        $appointment = Appointment::findOrFail($id);
        $user        = auth()->user();

        if (! $user->isSuperAdmin() && $appointment->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $start = Carbon::parse($newStart);
        $end   = Carbon::parse($newEnd);

        $appointment->update([
            'date'       => $start->format('Y-m-d'),
            'start_time' => $start->format('H:i'),
            'end_time'   => $end->format('H:i'),
        ]);

        Notification::make()
            ->title('Agendamento reagendado para ' . $start->format('d/m/Y') . ' às ' . $start->format('H:i'))
            ->success()
            ->send();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'    => 'Agendado',
            'confirmed'  => 'Confirmado',
            'arrived'    => 'Chegou',
            'in_service' => 'Em atendimento',
            'completed'  => 'Concluído',
            'cancelled'  => 'Cancelado',
            'no_show'    => 'Não compareceu',
            default      => $status,
        };
    }

    private function statusHexColor(string $status): string
    {
        return match ($status) {
            'pending'    => '#f59e0b',
            'confirmed'  => '#3b82f6',
            'arrived'    => '#8b5cf6',
            'in_service' => '#f97316',
            'completed'  => '#10b981',
            'cancelled'  => '#ef4444',
            'no_show'    => '#6b7280',
            default      => '#6b7280',
        };
    }
}
