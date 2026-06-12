<?php

namespace App\Filament\Resources\Professionals\Pages;

use App\Filament\Resources\Professionals\ProfessionalResource;
use App\Forms\Components\DateSlotPicker;
use App\Models\Professional;
use App\Services\SchedulingService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProfessional extends EditRecord
{
    protected static string $resource = ProfessionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('configure_schedule')
                ->label('Configurar Agenda')
                ->icon('heroicon-o-calendar-days')
                ->color('success')
                ->modalWidth('5xl')
                ->fillForm(fn (Professional $record): array => $this->defaultFormData($record))
                ->form($this->scheduleForm())
                ->action(function (array $data, Professional $record): void {
                    $service = app(SchedulingService::class);

                    if ($data['schedule_mode'] === 'periods') {
                        // Período manhã
                        $service->configureWeeklyAvailability(
                            professional: $record,
                            weekdays:     $data['weekdays'],
                            startTime:    $data['morning_start'],
                            endTime:      $data['morning_end'],
                        );

                        // Tarde (opcional)
                        if (! empty($data['has_afternoon'])) {
                            $service->configureWeeklyAvailability(
                                professional: $record,
                                weekdays:     $data['weekdays'],
                                startTime:    $data['afternoon_start'],
                                endTime:      $data['afternoon_end'],
                            );
                        }

                        // Bloqueia almoço
                        if (! empty($data['has_break'])) {
                            $service->configureBreak(
                                professional: $record,
                                weekdays:     $data['weekdays'],
                                breakStart:   $data['break_start'],
                                breakEnd:     $data['break_end'],
                            );
                        }
                    } else {
                        // Modo slots manuais — cada slot tem {time, duration} individuais
                        $config = json_decode($data['manual_slots_config'] ?? '{}', true) ?? [];
                        foreach ($config as $date => $slots) {
                            if (! empty($slots)) {
                                $service->configureManualSlotsForDate(
                                    professional: $record,
                                    date:         $date,
                                    slots:        $slots,
                                );
                            }
                        }
                    }

                    $record->update(['schedule_config' => $data]);

                    Notification::make()->title('Agenda configurada!')->success()->send();
                }),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    private function defaultFormData(Professional $record): array
    {
        $cfg = $record->schedule_config ?? [];

        return array_merge([
            'schedule_mode'   => 'periods',
            'weekdays'        => ['monday','tuesday','wednesday','thursday','friday'],
            'slot_duration'   => 30,
            // períodos
            'morning_start'   => '08:00',
            'morning_end'     => '12:00',
            'has_break'       => true,
            'break_start'     => '12:00',
            'break_end'       => '13:00',
            'has_afternoon'   => true,
            'afternoon_start' => '13:00',
            'afternoon_end'   => '18:00',
            // manual — JSON string: {"2026-06-05": [{"time":"09:00","duration":60},...], ...}
            'manual_slots_config' => '{}',
        ], $cfg);
    }

    private function scheduleForm(): array
    {
        $slotOptions = $this->generateSlotOptions();

        return [
            // ── Modo ──────────────────────────────────────────
            Radio::make('schedule_mode')
                ->label('Tipo de configuração')
                ->options([
                    'periods' => '🕐 Períodos (manhã / almoço / tarde)',
                    'manual'  => '🗓 Slots manuais (escolha horário por horário)',
                ])
                ->default('periods')
                ->live()
                ->columnSpanFull()
                ->inline(),

            // ── Dias e duração (sempre visíveis) ─────────────
            CheckboxList::make('weekdays')
                ->label('Dias de atendimento')
                ->options([
                    'monday'    => 'Segunda',
                    'tuesday'   => 'Terça',
                    'wednesday' => 'Quarta',
                    'thursday'  => 'Quinta',
                    'friday'    => 'Sexta',
                    'saturday'  => 'Sábado',
                    'sunday'    => 'Domingo',
                ])
                ->columns(4)
                ->required()
                ->visible(fn ($get) => $get('schedule_mode') === 'periods'),

            // ══ MODO PERÍODOS ═════════════════════════════════
            Grid::make(3)
                ->visible(fn ($get) => $get('schedule_mode') === 'periods')
                ->schema([
                    Fieldset::make('🌅 Manhã')
                        ->columns(2)
                        ->schema([
                            TimePicker::make('morning_start')
                                ->label('Início')
                                ->seconds(false)
                                ->default('08:00')
                                ->required(),
                            TimePicker::make('morning_end')
                                ->label('Término')
                                ->seconds(false)
                                ->default('12:00')
                                ->required(),
                        ]),

                    Fieldset::make('🍽 Intervalo de Almoço')
                        ->columns(2)
                        ->schema([
                            Toggle::make('has_break')
                                ->label('Tem intervalo?')
                                ->default(true)
                                ->live()
                                ->columnSpanFull(),
                            TimePicker::make('break_start')
                                ->label('Início')
                                ->seconds(false)
                                ->default('12:00')
                                ->visible(fn ($get) => (bool) $get('has_break')),
                            TimePicker::make('break_end')
                                ->label('Fim')
                                ->seconds(false)
                                ->default('13:00')
                                ->visible(fn ($get) => (bool) $get('has_break')),
                        ]),

                    Fieldset::make('🌇 Tarde')
                        ->columns(2)
                        ->schema([
                            Toggle::make('has_afternoon')
                                ->label('Tem período da tarde?')
                                ->default(true)
                                ->live()
                                ->columnSpanFull(),
                            TimePicker::make('afternoon_start')
                                ->label('Início')
                                ->seconds(false)
                                ->default('13:00')
                                ->visible(fn ($get) => (bool) $get('has_afternoon')),
                            TimePicker::make('afternoon_end')
                                ->label('Término')
                                ->seconds(false)
                                ->default('18:00')
                                ->visible(fn ($get) => (bool) $get('has_afternoon')),
                        ]),
                ]),

            // ══ MODO SLOTS MANUAIS ════════════════════════════
            Fieldset::make('📅 Selecione os dias e horários')
                ->visible(fn ($get) => $get('schedule_mode') === 'manual')
                ->schema([
                    DateSlotPicker::make('manual_slots_config')
                        ->label('')
                        ->columnSpanFull(),
                ]),
        ];
    }

    private function generateSlotOptions(): array
    {
        $options = [];
        $start   = 6 * 60; // 06:00
        $end     = 22 * 60; // 22:00

        for ($m = $start; $m < $end; $m += 30) {
            $h      = intdiv($m, 60);
            $min    = $m % 60;
            $label  = sprintf('%02d:%02d', $h, $min);
            $options[$label] = $label;
        }

        return $options;
    }
}
