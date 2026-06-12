<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Support\Permission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                TextColumn::make('role')
                    ->label('Função')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        User::ROLE_SUPER_ADMIN  => 'Super Admin',
                        User::ROLE_GESTOR       => 'Gestor',
                        User::ROLE_PROFISSIONAL => 'Profissional',
                        User::ROLE_CLIENTE      => 'Cliente',
                        default                 => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        User::ROLE_SUPER_ADMIN  => 'danger',
                        User::ROLE_GESTOR       => 'warning',
                        User::ROLE_PROFISSIONAL => 'info',
                        default                 => 'gray',
                    }),

                TextColumn::make('professional.name')
                    ->label('Perfil profissional')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->toggleable()
                    ->visible(fn () => Permission::isSuperAdmin()),

                TextColumn::make('permissions')
                    ->label('Permissões extras')
                    ->formatStateUsing(fn ($state) => $state ? count($state) . ' permissão(ões)' : '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
