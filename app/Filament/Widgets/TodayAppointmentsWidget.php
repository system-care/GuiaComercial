<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TodayAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Agenda de Hoje';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->whereDate('date', today())
                    ->whereNotIn('status', ['cancelled', 'no_show'])
                    ->with(['customer', 'service', 'professional'])
                    ->orderBy('start_time')
            )
            ->columns([
                TextColumn::make('start_time')
                    ->label('Horário')
                    ->formatStateUsing(fn ($state, $record) => substr($state, 0, 5) . ' – ' . substr($record->end_time, 0, 5))
                    ->width('120px'),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('service.name')
                    ->label('Serviço'),
                TextColumn::make('professional.name')
                    ->label('Profissional'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'    => 'Agendado',
                        'confirmed'  => 'Confirmado',
                        'arrived'    => 'Chegou',
                        'in_service' => 'Em atendimento',
                        'completed'  => 'Concluído',
                        default      => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'info',
                        'arrived'    => 'primary',
                        'in_service' => 'purple',
                        'completed'  => 'success',
                        default      => 'gray',
                    }),
            ])
            ->emptyStateHeading('Nenhum agendamento para hoje')
            ->emptyStateDescription('Os agendamentos de hoje aparecerão aqui.')
            ->paginated(false);
    }
}
