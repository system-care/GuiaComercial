<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Plano')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Ex: free_trial, starter, pro, ultra'),
                        TextInput::make('price')
                            ->label('Preço (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0)
                            ->required(),
                        Select::make('billing_cycle')
                            ->label('Ciclo de cobrança')
                            ->options(['monthly' => 'Mensal', 'yearly' => 'Anual'])
                            ->default('monthly')
                            ->required(),
                        TextInput::make('trial_days')
                            ->label('Dias de trial')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('sort_order')
                            ->label('Ordem de exibição')
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('Limites')
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->schema([
                        TextInput::make('max_appointments_month')
                            ->label('Agend./mês (0 = ilimitado)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('max_professionals')
                            ->label('Máx. profissionais')
                            ->numeric()
                            ->default(1),
                        TextInput::make('max_services')
                            ->label('Máx. serviços')
                            ->numeric()
                            ->default(5),
                    ]),

                Section::make('Features (exibidas na landing)')
                    ->schema([
                        Repeater::make('features')
                            ->label('')
                            ->simple(
                                TextInput::make('feature')->required()
                            )
                            ->addActionLabel('+ Adicionar feature')
                            ->nullable(),
                    ]),

                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }
}
