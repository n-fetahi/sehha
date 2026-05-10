<?php

namespace App\Filament\Resources\ExaminationTypes\Pages;

use App\Filament\Resources\ExaminationTypes\ExaminationTypeResource;
use App\Filament\Resources\ExaminationTypes\RelationManagers\ItemsRelationManager;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;

class ViewExaminationType extends ViewRecord
{
    protected static string $resource = ExaminationTypeResource::class;

    // ✅ أزرار أعلى الصفحة (يسار)
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),

            Action::make('create')
                ->label('إضافة')
                ->url(ExaminationTypeResource::getUrl('create')),
        ];
    }

    // ❌ لا نستخدم فورم هنا
    public function getForms(): array
    {
        return [];
    }

    // ✅ Relation Manager (الفحوصات)
    public function getRelationManagers(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    // ✅ عنوان الصفحة
    public function getTitle(): string
    {
        return $this->record->name;
    }
}