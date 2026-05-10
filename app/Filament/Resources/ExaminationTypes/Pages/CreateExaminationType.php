<?php

namespace App\Filament\Resources\ExaminationTypes\Pages;

use App\Filament\Resources\ExaminationTypes\ExaminationTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExaminationType extends CreateRecord
{
    protected static string $resource = ExaminationTypeResource::class;
}
