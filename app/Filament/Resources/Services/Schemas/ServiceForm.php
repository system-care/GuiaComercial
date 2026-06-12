<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do serviço')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Empresa')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull()
                            ->visible(fn () => auth()->user()?->isSuperAdmin()),
                        TextInput::make('name')
                            ->label('Nome do serviço')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('description')
                            ->label('Descrição')
                            ->nullable()
                            ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link'])
                            ->columnSpanFull(),
                        TextInput::make('duration_minutes')
                            ->label('Duração (minutos)')
                            ->numeric()
                            ->default(60)
                            ->minValue(5)
                            ->required(),
                        TextInput::make('buffer_minutes')
                            ->label('Intervalo pós-serviço (min)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('price')
                            ->label('Preço (R$)')
                            ->numeric()
                            ->nullable()
                            ->prefix('R$'),
                        ColorPicker::make('color')
                            ->label('Cor')
                            ->default('#3B82F6'),
                        Toggle::make('active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}
