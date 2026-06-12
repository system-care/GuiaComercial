<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Models\BusinessNiche;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome da empresa')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Usado na URL pública: /agendar/{slug}'),
                    ]),

                Section::make('Contato')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->nullable(),
                        TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->nullable(),
                        TextInput::make('city')
                            ->label('Cidade')
                            ->nullable(),
                        Select::make('timezone')
                            ->label('Fuso horário')
                            ->options([
                                'America/Sao_Paulo'   => 'Brasília (GMT-3)',
                                'America/Manaus'      => 'Manaus (GMT-4)',
                                'America/Belem'       => 'Belém (GMT-3)',
                                'America/Fortaleza'   => 'Fortaleza (GMT-3)',
                                'America/Recife'      => 'Recife (GMT-3)',
                                'America/Porto_Velho' => 'Porto Velho (GMT-4)',
                                'America/Boa_Vista'   => 'Boa Vista (GMT-4)',
                                'America/Rio_Branco'  => 'Rio Branco (GMT-5)',
                                'America/Noronha'     => 'Fernando de Noronha (GMT-2)',
                            ])
                            ->default('America/Sao_Paulo')
                            ->required(),
                    ]),

                Section::make('Configurações')
                    ->columns(2)
                    ->schema([
                        Select::make('business_niche_id')
                            ->label('Segmento de negócio')
                            ->options(fn () => \App\Models\NicheCategory::with(['niches' => fn ($q) => $q->where('active', true)->orderBy('name')])
                                ->active()
                                ->get()
                                ->mapWithKeys(fn ($cat) => [
                                    $cat->name => $cat->niches->pluck('name', 'id'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->nullable(),
                        Select::make('plan')
                            ->label('Plano')
                            ->options([
                                'free'    => 'Gratuito',
                                'starter' => 'Starter',
                                'pro'     => 'Pro',
                                'ultra'   => 'Ultra',
                            ])
                            ->default('free')
                            ->required(),
                        Toggle::make('active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}
