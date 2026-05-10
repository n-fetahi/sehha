<?php

namespace App\Filament\Resources\MedicalDepartments\Pages;

use App\Filament\Resources\MedicalDepartments\MedicalDepartmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMedicalDepartment extends ViewRecord
{
    protected static string $resource = MedicalDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
