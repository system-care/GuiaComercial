<?php

namespace App\Filament\Resources\Tenants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('niche.name')
                    ->label('Nicho')
                    ->badge()
                    ->sortable(),
                TextColumn::make('plan')
                    ->label('Plano')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'free'    => 'Gratuito',
                        'starter' => 'Starter',
                        'pro'     => 'Pro',
                        'ultra'   => 'Ultra',
                        default   => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'free'    => 'gray',
                        'starter' => 'info',
                        'pro'     => 'warning',
                        'ultra'   => 'success',
                        default   => 'gray',
                    }),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->label('Cidade')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->label('Plano')
                    ->options([
                        'free'    => 'Gratuito',
                        'starter' => 'Starter',
                        'pro'     => 'Pro',
                        'ultra'   => 'Ultra',
                    ]),
                SelectFilter::make('business_niche_id')
                    ->label('Nicho')
                    ->relationship('niche', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
