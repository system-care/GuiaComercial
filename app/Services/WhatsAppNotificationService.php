<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\MessageLog;
use App\Models\Subscription;
use App\Models\TenantSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppNotificationService
{
    public const DEFAULT_TEMPLATE =
        "Ol\u{00E1}, {nome}! \u{1F44B}\n\n" .
        "Confirmando seu agendamento em *{empresa}*:\n\n" .
        "\u{25C6} *Data:* {data}\n" .
        "\u{25C6} *Hor\u{00E1}rio:* {horario}\n" .
        "\u{25C6} *Servi\u{00E7}o:* {servico}\n" .
        "\u{25C6} *Profissional:* {profissional}\n" .
        "\u{25C6} *Endere\u{00E7}o:* {endereco}\n\n" .
        "Confirme ou cancele pelo link:\n{link}\n\n" .
        "_O link expira 30 minutos antes do hor\u{00E1}rio agendado._";

    public function __construct(private EvolutionService $evolution) {}

    public function shouldNotify(int $tenantId): bool
    {
        $tenant = \App\Models\Tenant::find($tenantId);
        if (! $tenant) {
            return false;
        }

        $sub = $tenant->activeSubscription();

        return $sub && $sub->status === Subscription::STATUS_ACTIVE;
    }

    public function buildBody(Appointment $appointment, string $confirmUrl, ?string $template = null): string
    {
        $appointment->loadMissing(['tenant', 'customer', 'service', 'professional']);

        $template = $template ?: static::DEFAULT_TEMPLATE;

        $settings = TenantSetting::where('tenant_id', $appointment->tenant_id)->value('settings') ?? [];

        return str_replace(
            ['{nome}', '{empresa}', '{data}', '{horario}', '{servico}', '{profissional}', '{endereco}', '{link}'],
            [
                $appointment->customer->name     ?? 'você',
                $appointment->tenant->name       ?? 'nossa empresa',
                Carbon::parse($appointment->date)->format('d/m/Y'),
                substr($appointment->start_time, 0, 5),
                $appointment->service->name      ?? '',
                $appointment->professional->name ?? '',
                $settings['address']             ?? '',
                $confirmUrl,
            ],
            $template
        );
    }

    public function sendConfirmation(Appointment $appointment): void
    {
        if (! $this->shouldNotify($appointment->tenant_id)) {
            return;
        }

        $appointment->loadMissing(['tenant', 'customer', 'service', 'professional']);

        $phone = preg_replace('/\D/', '', $appointment->customer->phone ?? '');
        if (! $phone) {
            return;
        }

        $baseUrl = config('services.evolution.base_url');
        $token   = config('services.evolution.token');

        if (! $baseUrl || ! $token) {
            return;
        }

        $settings = TenantSetting::where('tenant_id', $appointment->tenant_id)->value('settings') ?? [];

        $enabled = (bool) ($settings['evolution_enabled'] ?? false);

        if (! $enabled) {
            return;
        }

        $slug     = $appointment->tenant->slug ?? 'tenant';
        $instance = 'gc_' . $slug . '_' . $appointment->tenant_id;

        if (! $appointment->confirmation_token) {
            $appointment->update(['confirmation_token' => Str::random(48)]);
            $appointment->refresh();
        }

        if (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }

        $confirmUrl = route('appointment.confirm', ['token' => $appointment->confirmation_token]);
        $template   = $settings['confirmation_message'] ?? null;
        $body       = $this->buildBody($appointment, $confirmUrl, $template ?: null);

        $log = MessageLog::create([
            'tenant_id'      => $appointment->tenant_id,
            'appointment_id' => $appointment->id,
            'channel'        => 'whatsapp',
            'phone'          => $phone,
            'body'           => $body,
            'status'         => 'pending',
            'provider'       => 'evolution',
        ]);

        try {
            $result = $this->evolution->sendText($baseUrl, $token, $instance, $phone, $body);

            $log->update([
                'status'              => 'sent',
                'provider_message_id' => $result['key']['id'] ?? $result['messageId'] ?? null,
                'sent_at'             => now(),
            ]);
        } catch (\Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('WhatsApp confirmation send failed', [
                'appointment_id' => $appointment->id,
                'instance'       => $instance,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
