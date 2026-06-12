<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\WhatsAppNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsAppConfirmationJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 60;
    public array $backoff = [60, 300, 900];

    public function __construct(public readonly int $appointmentId) {}

    public function handle(WhatsAppNotificationService $service): void
    {
        $appointment = Appointment::find($this->appointmentId);
        if (! $appointment) {
            return;
        }

        $service->sendConfirmation($appointment);
    }
}
