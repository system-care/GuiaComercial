<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class DateSlotPicker extends Field
{
    protected string $view = 'forms.components.date-slot-picker';

    public function getSlotOptions(): array
    {
        $options = [];
        for ($m = 6 * 60; $m < 22 * 60; $m += 30) {
            $options[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
        }
        return $options;
    }
}
