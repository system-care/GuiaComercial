<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do cliente')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Empresa')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn () => auth()->user()?->isSuperAdmin()),
                        TextInput::make('name')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->nullable(),
                        TextInput::make('phone')
                            ->label('Telefone / WhatsApp')
                            ->tel()
                            ->nullable(),
                        TextInput::make('document')
                            ->label('CPF / Documento')
                            ->nullable(),
                        DatePicker::make('birth_date')
                            ->label('Data de nascimento')
                            ->displayFormat('d/m/Y')
                            ->nullable(),
                        Toggle::make('active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}
