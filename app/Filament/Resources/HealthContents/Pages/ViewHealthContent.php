<?php

namespace App\Filament\Resources\HealthContents\Pages;

use App\Filament\Resources\HealthContents\HealthContentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHealthContent extends ViewRecord
{
    protected static string $resource = HealthContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
