<?php

namespace App\Filament\Resources\MedicalDepartments\Pages;

use App\Filament\Resources\MedicalDepartments\MedicalDepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicalDepartments extends ListRecords
{
    protected static string $resource = MedicalDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
