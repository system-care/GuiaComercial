<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    public function mount(): void
    {
        parent::mount();

        $defaults = [];

        if ($date = request()->query('date')) {
            $defaults['date'] = $date;
        }

        if ($start = request()->query('start_time')) {
            $defaults['start_time'] = $start;
        }

        if ($defaults) {
            $this->form->fill($defaults);
        }
    }
}
