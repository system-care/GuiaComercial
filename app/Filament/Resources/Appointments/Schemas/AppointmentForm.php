<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Agendamento')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Empresa')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->columnSpanFull()
                            ->visible(fn () => auth()->user()?->isSuperAdmin()),

                        Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('service_id')
                            ->label('Serviço')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('professional_id')
                            ->label('Profissional')
                            ->relationship('professional', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending'    => 'Agendado',
                                'confirmed'  => 'Confirmado',
                                'arrived'    => 'Chegou',
                                'in_service' => 'Em atendimento',
                                'completed'  => 'Concluído',
                                'cancelled'  => 'Cancelado',
                                'no_show'    => 'Não compareceu',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),

                Section::make('Data e horário')
                    ->columns(['default' => 1, 'sm' => 3])
                    ->schema([
                        DatePicker::make('date')
                            ->label('Data')
                            ->displayFormat('d/m/Y')
                            ->required(),
                        TimePicker::make('start_time')
                            ->label('Início')
                            ->seconds(false)
                            ->required(),
                        TimePicker::make('end_time')
                            ->label('Término')
                            ->seconds(false)
                            ->required(),
                    ]),

                Section::make('Observações')
                    ->schema([
                        RichEditor::make('notes')
                            ->label('Observações')
                            ->nullable()
                            ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList']),
                    ]),
            ]);
    }
}
