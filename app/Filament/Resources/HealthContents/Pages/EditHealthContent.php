<?php

namespace App\Filament\Resources\HealthContents\Pages;

use App\Filament\Resources\HealthContents\HealthContentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditHealthContent extends EditRecord
{
    protected static string $resource = HealthContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
