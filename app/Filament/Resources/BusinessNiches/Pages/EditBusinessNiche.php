<?php

namespace App\Filament\Resources\BusinessNiches\Pages;

use App\Filament\Resources\BusinessNiches\BusinessNicheResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessNiche extends EditRecord
{
    protected static string $resource = BusinessNicheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
