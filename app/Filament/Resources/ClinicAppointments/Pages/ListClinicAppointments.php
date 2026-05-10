<?php

namespace App\Filament\Resources\ClinicAppointments\Pages;

use App\Filament\Resources\ClinicAppointments\ClinicAppointmentResource;
use Filament\Resources\Pages\ListRecords;

class ListClinicAppointments extends ListRecords
{
    protected static string $resource = ClinicAppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return []; // ❌ يمنع زر Create
    }
}