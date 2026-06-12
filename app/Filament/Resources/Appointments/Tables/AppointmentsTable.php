<?php

namespace App\Filament\Resources\Appointments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Horário')
                    ->formatStateUsing(fn ($state, $record) => substr($state, 0, 5) . ' – ' . substr($record->end_time, 0, 5)),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service.name')
                    ->label('Serviço')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('professional.name')
                    ->label('Profissional')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'    => 'Agendado',
                        'confirmed'  => 'Confirmado',
                        'arrived'    => 'Chegou',
                        'in_service' => 'Em atendimento',
                        'completed'  => 'Concluído',
                        'cancelled'  => 'Cancelado',
                        'no_show'    => 'Não compareceu',
                        default      => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'info',
                        'arrived'    => 'primary',
                        'in_service' => 'purple',
                        'completed'  => 'success',
                        'cancelled'  => 'danger',
                        'no_show'    => 'gray',
                        default      => 'secondary',
                    }),
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'    => 'Agendado',
                        'confirmed'  => 'Confirmado',
                        'arrived'    => 'Chegou',
                        'in_service' => 'Em atendimento',
                        'completed'  => 'Concluído',
                        'cancelled'  => 'Cancelado',
                        'no_show'    => 'Não compareceu',
                    ]),
                SelectFilter::make('professional_id')
                    ->label('Profissional')
                    ->relationship('professional', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                // Ações rápidas de status
                Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->action(fn ($record) => self::changeStatus($record, 'confirmed'))
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation(false),

                Action::make('arrived')
                    ->label('Chegou')
                    ->icon('heroicon-o-user-circle')
                    ->color('primary')
                    ->action(fn ($record) => self::changeStatus($record, 'arrived'))
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed']))
                    ->requiresConfirmation(false),

                Action::make('in_service')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play-circle')
                    ->color('warning')
                    ->action(fn ($record) => self::changeStatus($record, 'in_service'))
                    ->visible(fn ($record) => $record->status === 'arrived')
                    ->requiresConfirmation(false),

                Action::make('complete')
                    ->label('Concluir')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(fn ($record) => self::changeStatus($record, 'completed'))
                    ->visible(fn ($record) => in_array($record->status, ['arrived', 'in_service']))
                    ->requiresConfirmation(false),

                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn ($record) => self::changeStatus($record, 'cancelled'))
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed']))
                    ->requiresConfirmation(),

                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    private static function changeStatus($record, string $status): void
    {
        $record->update(['status' => $status]);

        $labels = [
            'confirmed'  => 'Confirmado!',
            'arrived'    => 'Cliente registrado como chegou.',
            'in_service' => 'Atendimento iniciado.',
            'completed'  => 'Atendimento concluído!',
            'cancelled'  => 'Agendamento cancelado.',
        ];

        Notification::make()
            ->title($labels[$status] ?? 'Status atualizado.')
            ->success()
            ->send();
    }
}
