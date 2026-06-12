<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use Carbon\Carbon;

class SchedulingService
{
    public function configureWeeklyAvailability(
        Professional $professional,
        array $weekdays,
        string $startTime,
        string $endTime,
        ?string $breakStart = null,
        ?string $breakEnd = null,
        ?string $fromDate = null
    ): void {
        $from = $fromDate ?? now()->format('Y-m-d');

        zap()->for($professional)
            ->availability()
            ->from($from)
            ->weekDays($weekdays, $startTime, $endTime)
            ->save();

        if ($breakStart && $breakEnd) {
            zap()->for($professional)
                ->blocked()
                ->from($from)
                ->weekly($weekdays)
                ->addPeriod($breakStart, $breakEnd)
                ->save();
        }
    }

    public function configureBreak(
        Professional $professional,
        array $weekdays,
        string $breakStart,
        string $breakEnd,
        ?string $fromDate = null
    ): void {
        $from = $fromDate ?? now()->format('Y-m-d');

        zap()->for($professional)
            ->blocked()
            ->from($from)
            ->weekly($weekdays)
            ->addPeriod($breakStart, $breakEnd)
            ->save();
    }

    /**
     * Converte slots manuais selecionados em períodos contíguos e configura no Zap.
     * Slots consecutivos são agrupados em um único período de disponibilidade.
     */
    public function configureManualSlots(
        Professional $professional,
        array $weekdays,
        array $slots,
        int $slotDuration = 30,
        ?string $fromDate = null
    ): void {
        if (empty($slots) || empty($weekdays)) {
            return;
        }

        $from   = $fromDate ?? now()->format('Y-m-d');
        $ranges = $this->slotsToRanges($slots, $slotDuration);

        foreach ($ranges as [$rangeStart, $rangeEnd]) {
            zap()->for($professional)
                ->availability()
                ->from($from)
                ->weekDays($weekdays, $rangeStart, $rangeEnd)
                ->save();
        }
    }

    /**
     * Agrupa slots consecutivos em intervalos contíguos.
     * Ex: ['09:00','09:30','10:00','11:00'] com duration=30
     *   → [['09:00','10:30'], ['11:00','11:30']]
     */
    private function slotsToRanges(array $slots, int $duration): array
    {
        if (empty($slots)) {
            return [];
        }

        sort($slots);

        $toMin = fn (string $t): int => (int) explode(':', $t)[0] * 60 + (int) explode(':', $t)[1];
        $toStr = fn (int $m): string => sprintf('%02d:%02d', intdiv($m, 60), $m % 60);

        $ranges     = [];
        $rangeStart = $toMin($slots[0]);
        $prev       = $rangeStart;

        for ($i = 1; $i < count($slots); $i++) {
            $cur = $toMin($slots[$i]);
            if ($cur === $prev + $duration) {
                $prev = $cur;
            } else {
                $ranges[] = [$toStr($rangeStart), $toStr($prev + $duration)];
                $rangeStart = $cur;
                $prev       = $cur;
            }
        }

        $ranges[] = [$toStr($rangeStart), $toStr($prev + $duration)];

        return $ranges;
    }

    /**
     * Configura disponibilidade para uma data específica.
     * $slots = [['time' => '09:00', 'duration' => 60], ...]
     * Cada entrada representa um período independente com duração própria.
     */
    public function configureManualSlotsForDate(
        Professional $professional,
        string $date,
        array $slots,
    ): void {
        if (empty($slots)) {
            return;
        }

        foreach ($slots as $slot) {
            $start    = $slot['time'];
            $duration = (int) ($slot['duration'] ?? 30);
            [$h, $m]  = explode(':', $start);
            $endMin   = (int)$h * 60 + (int)$m + $duration;
            $end      = sprintf('%02d:%02d', intdiv($endMin, 60), $endMin % 60);

            zap()->for($professional)
                ->availability()
                ->on($date)
                ->addPeriod($start, $end)
                ->save();
        }
    }

    public function getAvailableSlots(
        Professional $professional,
        string $date,
        int $durationMinutes
    ): array {
        return $professional->getBookableSlots(date: $date, slotDuration: $durationMinutes);
    }

    public function isSlotAvailable(
        Professional $professional,
        string $date,
        string $startTime,
        string $endTime
    ): bool {
        return $professional->isBookableAtTime(date: $date, startTime: $startTime, endTime: $endTime);
    }

    public function book(
        Professional $professional,
        Service $service,
        string $date,
        string $startTime,
        string $endTime,
        array $metadata = []
    ): bool {
        if (! $this->isSlotAvailable($professional, $date, $startTime, $endTime)) {
            return false;
        }

        zap()->for($professional)
            ->appointment()
            ->on($date)
            ->addPeriod($startTime, $endTime)
            ->withMetadata(array_merge(['service_id' => $service->id], $metadata))
            ->save();

        return true;
    }

    public function cancelAppointment(Appointment $appointment): void
    {
        zap()->for($appointment->professional)
            ->appointment()
            ->on($appointment->date->format('Y-m-d'))
            ->removePeriod($appointment->start_time, $appointment->end_time);
    }

    public function getNextAvailableSlot(Professional $professional, int $durationMinutes): ?array
    {
        $slot = $professional->getNextBookableSlot(
            afterDate: now()->format('Y-m-d'),
            duration: $durationMinutes
        );

        return $slot ?: null;
    }

    public function getStatusOptions(): array
    {
        return [
            'pending'   => 'Agendado',
            'confirmed' => 'Confirmado',
            'arrived'   => 'Chegou',
            'in_service' => 'Em atendimento',
            'completed' => 'Concluído',
            'cancelled'  => 'Cancelado',
            'no_show'   => 'Não compareceu',
        ];
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'pending'    => 'warning',
            'confirmed'  => 'info',
            'arrived'    => 'primary',
            'in_service' => 'purple',
            'completed'  => 'success',
            'cancelled'  => 'danger',
            'no_show'    => 'gray',
            default      => 'secondary',
        };
    }
}
