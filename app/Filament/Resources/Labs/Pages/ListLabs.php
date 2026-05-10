<?php

namespace App\Filament\Resources\Labs\Pages;

use App\Filament\Resources\Labs\LabResource;
use Filament\Resources\Pages\ListRecords;

class ListLabs extends ListRecords
{
    protected static string $resource = LabResource::class;

    // ❌ حذف زر الإضافة نهائيًا
    protected function getHeaderActions(): array
    {
        return [];
    }
}