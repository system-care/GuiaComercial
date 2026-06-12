<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('Cor'),
                TextColumn::make('name')
                    ->label('Serviço')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label('Duração')
                    ->formatStateUsing(fn ($state) => "{$state} min")
                    ->sortable(),
                TextColumn::make('buffer_minutes')
                    ->label('Intervalo')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} min" : '—')
                    ->toggleable(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
