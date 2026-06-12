<?php

namespace App\Filament\Resources\BusinessNiches\Pages;

use App\Filament\Resources\BusinessNiches\BusinessNicheResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusinessNiches extends ListRecords
{
    protected static string $resource = BusinessNicheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
