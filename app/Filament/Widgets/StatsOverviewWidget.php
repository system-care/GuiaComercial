<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $todayCount = Appointment::whereDate('date', $today)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        $monthCount = Appointment::where('date', '>=', $thisMonth)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $customerCount = Customer::where('active', true)->count();

        $monthRevenue = Appointment::where('date', '>=', $thisMonth)
            ->where('status', 'completed')
            ->with('service')
            ->get()
            ->sum(fn ($a) => $a->service?->price ?? 0);

        $tenantCount = Tenant::where('active', true)->count();

        return [
            Stat::make('Agendamentos hoje', $todayCount)
                ->description('Ativos (não cancelados)')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Agendamentos no mês', $monthCount)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Total de clientes', $customerCount)
                ->description('Clientes ativos')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Receita do mês', 'R$ ' . number_format($monthRevenue, 2, ',', '.'))
                ->description('Agendamentos concluídos')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
