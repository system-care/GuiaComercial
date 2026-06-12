<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subscription.plan.name')
                    ->label('Plano')
                    ->badge(),
                TextColumn::make('value')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'PENDING'   => 'Pendente',
                        'RECEIVED'  => 'Recebido',
                        'CONFIRMED' => 'Confirmado',
                        'OVERDUE'   => 'Vencido',
                        'REFUNDED'  => 'Estornado',
                        'CANCELED'  => 'Cancelado',
                        default     => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'PENDING'   => 'warning',
                        'RECEIVED'  => 'info',
                        'CONFIRMED' => 'success',
                        'OVERDUE'   => 'danger',
                        'REFUNDED'  => 'gray',
                        'CANCELED'  => 'gray',
                        default     => 'gray',
                    }),
                TextColumn::make('billing_type')
                    ->label('Método')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('asaas_payment_id')
                    ->label('ID ASAAS')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'PENDING'   => 'Pendente',
                        'RECEIVED'  => 'Recebido',
                        'CONFIRMED' => 'Confirmado',
                        'OVERDUE'   => 'Vencido',
                        'CANCELED'  => 'Cancelado',
                    ]),
                SelectFilter::make('billing_type')
                    ->label('Método')
                    ->options([
                        'PIX'         => 'PIX',
                        'BOLETO'      => 'Boleto',
                        'CREDIT_CARD' => 'Cartão',
                    ]),
            ])
            ->recordActions([
                Action::make('invoice')
                    ->label('Ver boleto/PIX')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->invoice_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => (bool) $record->invoice_url),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
