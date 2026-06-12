<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plan.name')
                    ->label('Plano')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        Subscription::STATUS_TRIAL           => 'Trial',
                        Subscription::STATUS_ACTIVE          => 'Ativo',
                        Subscription::STATUS_PENDING_PAYMENT => 'Aguard. pagamento',
                        Subscription::STATUS_OVERDUE         => 'Inadimplente',
                        Subscription::STATUS_CANCELED        => 'Cancelado',
                        Subscription::STATUS_SUSPENDED       => 'Suspenso',
                        default                              => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        Subscription::STATUS_TRIAL           => 'info',
                        Subscription::STATUS_ACTIVE          => 'success',
                        Subscription::STATUS_PENDING_PAYMENT => 'warning',
                        Subscription::STATUS_OVERDUE         => 'danger',
                        Subscription::STATUS_CANCELED        => 'gray',
                        Subscription::STATUS_SUSPENDED       => 'danger',
                        default                              => 'gray',
                    }),
                TextColumn::make('billing_type')
                    ->label('Método')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('trial_ends_at')
                    ->label('Fim do trial')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('current_period_end')
                    ->label('Próx. cobrança')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('payments_count')
                    ->label('Pagamentos')
                    ->counts('payments')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Subscription::STATUS_TRIAL           => 'Trial',
                        Subscription::STATUS_ACTIVE          => 'Ativo',
                        Subscription::STATUS_PENDING_PAYMENT => 'Aguard. pagamento',
                        Subscription::STATUS_OVERDUE         => 'Inadimplente',
                        Subscription::STATUS_CANCELED        => 'Cancelado',
                        Subscription::STATUS_SUSPENDED       => 'Suspenso',
                    ]),
                SelectFilter::make('plan_id')
                    ->label('Plano')
                    ->relationship('plan', 'name'),
            ])
            ->recordActions([
                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! in_array($record->status, [
                        Subscription::STATUS_CANCELED,
                        Subscription::STATUS_SUSPENDED,
                    ]))
                    ->action(function ($record) {
                        app(SubscriptionService::class)->cancel($record);
                        Notification::make()->title('Assinatura cancelada.')->success()->send();
                    }),

                Action::make('suspend')
                    ->label('Suspender')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === Subscription::STATUS_ACTIVE)
                    ->action(function ($record) {
                        $record->update(['status' => Subscription::STATUS_SUSPENDED]);
                        Notification::make()->title('Assinatura suspensa.')->warning()->send();
                    }),

                Action::make('reactivate')
                    ->label('Reativar')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, [
                        Subscription::STATUS_SUSPENDED,
                        Subscription::STATUS_OVERDUE,
                    ]))
                    ->action(function ($record) {
                        $record->update([
                            'status'        => Subscription::STATUS_ACTIVE,
                            'overdue_since' => null,
                        ]);
                        Notification::make()->title('Assinatura reativada!')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
