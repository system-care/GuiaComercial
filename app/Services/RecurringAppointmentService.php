<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RecurringAppointmentService
{
    const MAX_OCCURRENCES = 52;

    public function createSeries(array $data, int $tenantId): array
    {
        $groupId = (string) Str::uuid();
        $base    = Carbon::parse($data['date']);
        $dates   = $this->buildDates($base, $data);

        $created = [];
        $skipped = [];

        foreach ($dates as $index => $date) {
            $conflict = Appointment::where('professional_id', $data['professional_id'])
                ->where('date', $date->format('Y-m-d'))
                ->whereNotIn('status', ['cancelled'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->exists();

            if ($conflict) {
                $skipped[] = $date->format('d/m/Y');
                continue;
            }

            Appointment::create([
                'tenant_id'           => $tenantId,
                'customer_id'         => $data['customer_id'],
                'service_id'          => $data['service_id'] ?: null,
                'professional_id'     => $data['professional_id'] ?: null,
                'date'                => $date->format('Y-m-d'),
                'start_time'          => $data['start_time'],
                'end_time'            => $data['end_time'],
                'status'              => $data['status'] ?? 'pending',
                'notes'               => $data['notes'] ?? null,
                'recurrence_group_id' => $groupId,
                'recurrence_index'    => $index,
            ]);

            $created[] = $date->format('d/m/Y');
        }

        return compact('created', 'skipped', 'groupId');
    }

    private function buildDates(Carbon $base, array $data): array
    {
        $dates   = [];
        $current = $base->copy();
        $mode    = $data['recurrence_mode'] ?? 'count';
        $limit   = self::MAX_OCCURRENCES;

        if ($mode === 'end_date' && ! empty($data['recurrence_end_date'])) {
            $end = Carbon::parse($data['recurrence_end_date']);
            $i   = 0;
            while ($current->lte($end) && $i++ < $limit) {
                $dates[] = $current->copy();
                $current->addWeek();
            }
        } elseif (! empty($data['recurrence_count'])) {
            $count = min((int) $data['recurrence_count'], $limit);
            for ($i = 0; $i < $count; $i++) {
                $dates[] = $current->copy();
                $current->addWeek();
            }
        }

        return $dates;
    }
}
