<?php

namespace App\Filament\Resources\ExaminationTypes\Pages;

use App\Filament\Resources\ExaminationTypes\ExaminationTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExaminationType extends EditRecord
{
    protected static string $resource = ExaminationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
