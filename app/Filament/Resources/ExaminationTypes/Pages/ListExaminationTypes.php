<?php

namespace App\Filament\Resources\ExaminationTypes\Pages;

use App\Filament\Resources\ExaminationTypes\ExaminationTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExaminationTypes extends ListRecords
{
    protected static string $resource = ExaminationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
