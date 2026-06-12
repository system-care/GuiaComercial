<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AppointmentsChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Agendamentos — Últimos 7 dias';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '180px';

    protected function getData(): array
    {
        $days   = collect();
        $counts = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days->push($date->translatedFormat('d/m'));
            $counts->push(
                Appointment::whereDate('date', $date)
                    ->whereNotIn('status', ['cancelled', 'no_show'])
                    ->count()
            );
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Agendamentos',
                    'data'            => $counts->toArray(),
                    'backgroundColor' => '#8b5cf6',
                    'borderColor'     => '#7c3aed',
                    'borderWidth'     => 2,
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $days->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
