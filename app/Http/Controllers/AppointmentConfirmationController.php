<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentConfirmationController extends Controller
{
    public function show(string $token)
    {
        $appointment = Appointment::where('confirmation_token', $token)
            ->with(['tenant.settings', 'customer', 'service', 'professional'])
            ->firstOrFail();

        return view('appointment.confirm', compact('appointment', 'token'));
    }

    public function confirm(Request $request, string $token)
    {
        $appointment = Appointment::where('confirmation_token', $token)->firstOrFail();

        if (in_array($appointment->status, ['cancelled', 'no_show', 'completed'])) {
            return view('appointment.confirm-result', [
                'type'    => 'error',
                'message' => 'Este agendamento não pode mais ser alterado.',
            ]);
        }

        if ($this->deadlineExpired($appointment)) {
            return view('appointment.confirm-result', [
                'type'    => 'error',
                'message' => 'O prazo para confirmar este agendamento encerrou.',
            ]);
        }

        $appointment->update([
            'status'               => 'confirmed',
            'confirmation_status'  => 'confirmed',
        ]);

        return view('appointment.confirm-result', [
            'type'        => 'success',
            'message'     => 'Agendamento confirmado! Até logo. 😊',
            'appointment' => $appointment->load(['tenant', 'service']),
        ]);
    }

    public function cancel(Request $request, string $token)
    {
        $appointment = Appointment::where('confirmation_token', $token)->firstOrFail();

        if (in_array($appointment->status, ['cancelled', 'no_show', 'completed'])) {
            return view('appointment.confirm-result', [
                'type'    => 'error',
                'message' => 'Este agendamento não pode mais ser alterado.',
            ]);
        }

        if ($this->deadlineExpired($appointment)) {
            return view('appointment.confirm-result', [
                'type'    => 'error',
                'message' => 'O prazo para cancelar este agendamento encerrou.',
            ]);
        }

        $appointment->update([
            'status'              => 'cancelled',
            'confirmation_status' => 'cancelled_by_patient',
        ]);

        return view('appointment.confirm-result', [
            'type'    => 'cancelled',
            'message' => 'Agendamento cancelado. Se quiser remarcar, entre em contato.',
            'appointment' => $appointment->load(['tenant']),
        ]);
    }

    /**
     * Retorna true se a janela de confirmação já fechou.
     *
     * - Reagendado nas últimas 24h: prazo = horário exato do início (sem buffer).
     *   Permite confirmar mesmo que o reagendamento tenha ocorrido a menos de 30 min do novo horário.
     * - Normal: prazo = 30 min antes do início.
     */
    private function deadlineExpired(Appointment $appointment): bool
    {
        $appointmentAt = Carbon::parse($appointment->date->format('Y-m-d') . ' ' . $appointment->start_time);

        $rescheduledAt = isset($appointment->custom_data['rescheduled_at'])
            ? Carbon::parse($appointment->custom_data['rescheduled_at'])
            : null;

        $recentlyRescheduled = $rescheduledAt && $rescheduledAt->gte(now()->subDay());

        $deadline = $recentlyRescheduled
            ? $appointmentAt                   // até o início exato
            : $appointmentAt->subMinutes(30);  // 30 min de antecedência normal

        return now()->gte($deadline);
    }
}
