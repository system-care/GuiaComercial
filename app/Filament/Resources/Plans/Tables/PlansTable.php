<?php

namespace App\Filament\Resources\Plans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),
                TextColumn::make('name')
                    ->label('Plano')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('trial_days')
                    ->label('Trial')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} dias" : '—')
                    ->toggleable(),
                TextColumn::make('max_professionals')
                    ->label('Profissionais')
                    ->formatStateUsing(fn ($state) => $state === 0 ? '∞' : $state)
                    ->toggleable(),
                TextColumn::make('max_appointments_month')
                    ->label('Agend./mês')
                    ->formatStateUsing(fn ($state) => $state === 0 ? '∞' : $state)
                    ->toggleable(),
                TextColumn::make('subscriptions_count')
                    ->label('Assinantes')
                    ->counts('subscriptions')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
