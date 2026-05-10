<?php

namespace App\Filament\Resources\ExaminationTypes;

use App\Filament\Resources\ExaminationTypes\Pages\CreateExaminationType;
use App\Filament\Resources\ExaminationTypes\Pages\EditExaminationType;
use App\Filament\Resources\ExaminationTypes\Pages\ListExaminationTypes;
use App\Filament\Resources\ExaminationTypes\Pages\ViewExaminationType;
use App\Filament\Resources\ExaminationTypes\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\ExaminationTypes\Schemas\ExaminationTypeForm;
use App\Filament\Resources\ExaminationTypes\Tables\ExaminationTypesTable;
use App\Models\ExaminationType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExaminationTypeResource extends Resource
{
    protected static ?string $model = ExaminationType::class;

    // ✅ أيقونة مناسبة للفحوصات
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    // ✅ تعريب
    protected static ?string $navigationLabel = 'أنواع الفحوصات';
    protected static ?string $modelLabel = 'نوع فحص';
    protected static ?string $pluralModelLabel = 'أنواع الفحوصات';

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return ExaminationTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExaminationTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    // ✅ الصفحات
    public static function getPages(): array
    {
        return [
            'index' => ListExaminationTypes::route('/'),
            'create' => CreateExaminationType::route('/create'),
            'edit' => EditExaminationType::route('/{record}/edit'),
            'view' => ViewExaminationType::route('/{record}'),
        ];
    }
}