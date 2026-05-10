<?php

namespace App\Filament\Resources\HealthContents\Pages;

use App\Filament\Resources\HealthContents\HealthContentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHealthContents extends ListRecords
{
    protected static string $resource = HealthContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
