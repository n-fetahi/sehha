<?php

namespace App\Filament\Resources\LabAppointments\Pages;

use App\Filament\Resources\LabAppointments\LabAppointmentResource;
use Filament\Resources\Pages\ListRecords;

class ListLabAppointments extends ListRecords
{
    protected static string $resource = LabAppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return []; // ❌ حذف زر الإضافة
    }
}