<?php

namespace App\Filament\Resources\MedicalDepartments;

use App\Filament\Resources\MedicalDepartments\Pages\CreateMedicalDepartment;
use App\Filament\Resources\MedicalDepartments\Pages\ListMedicalDepartments;
use App\Filament\Resources\MedicalDepartments\Schemas\MedicalDepartmentForm;
use App\Filament\Resources\MedicalDepartments\Tables\MedicalDepartmentsTable;
use App\Models\MedicalDepartment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MedicalDepartmentResource extends Resource
{
    protected static ?string $model = MedicalDepartment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ تعريب القائمة
    protected static ?string $navigationLabel = 'الأقسام الطبية';
    protected static ?string $modelLabel = 'قسم طبي';
    protected static ?string $pluralModelLabel = 'الأقسام الطبية';

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return MedicalDepartmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalDepartmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicalDepartments::route('/'),
            'create' => CreateMedicalDepartment::route('/create'),
        ];
    }
}