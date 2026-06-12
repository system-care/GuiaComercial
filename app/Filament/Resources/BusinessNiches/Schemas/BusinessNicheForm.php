<?php

namespace App\Filament\Resources\BusinessNiches\Schemas;

use App\Models\NicheCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BusinessNicheForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('niche_category_id')
                    ->label('Categoria')
                    ->options(NicheCategory::active()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->columnSpanFull(),

                TextInput::make('key')
                    ->label('Chave (slug)')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('name')
                    ->label('Nome')
                    ->required(),

                TextInput::make('icon')
                    ->label('Ícone (heroicon)')
                    ->placeholder('heroicon-o-heart')
                    ->default(null),

                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }
}
