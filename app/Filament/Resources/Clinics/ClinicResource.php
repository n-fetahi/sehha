<?php

namespace App\Filament\Resources\Clinics;

use App\Filament\Resources\Clinics\Pages\ListClinics;
use App\Filament\Resources\Clinics\Pages\ViewClinic;
use App\Filament\Resources\Clinics\Schemas\ClinicForm;
use App\Filament\Resources\Clinics\Schemas\ClinicInfolist;
use App\Filament\Resources\Clinics\Tables\ClinicsTable;
use App\Models\Clinic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClinicResource extends Resource
{
    protected static ?string $model = Clinic::class;

    // ✅ أيقونة مناسبة للعيادات
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    // ✅ تعريب
    protected static ?string $navigationLabel = 'العيادات';
    protected static ?string $modelLabel = 'عيادة';
    protected static ?string $pluralModelLabel = 'العيادات';

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المستخدمين';
    }

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ClinicForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClinicInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClinicsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    // ✅ الصفحات (عرض فقط)
    public static function getPages(): array
    {
        return [
            'index' => ListClinics::route('/'),
            'view' => ViewClinic::route('/{record}'),
        ];
    }

    // ❌ منع الإضافة
    public static function canCreate(): bool
    {
        return false;
    }

    // ❌ منع التعديل
    public static function canEdit($record): bool
    {
        return false;
    }

    // ❌ منع الحذف
    public static function canDelete($record): bool
    {
        return false;
    }
}