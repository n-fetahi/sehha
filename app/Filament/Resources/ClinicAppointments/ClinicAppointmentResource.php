<?php

namespace App\Filament\Resources\ClinicAppointments;

use App\Filament\Resources\ClinicAppointments\Pages\ListClinicAppointments;
use App\Filament\Resources\ClinicAppointments\Schemas\ClinicAppointmentForm;
use App\Filament\Resources\ClinicAppointments\Tables\ClinicAppointmentsTable;
use App\Models\ClinicAppointment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClinicAppointmentResource extends Resource
{
    protected static ?string $model = ClinicAppointment::class;

    // ✅ أيقونة مناسبة للحجوزات
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    // ✅ تعريب الواجهة
    protected static ?string $navigationLabel = 'حجوزات العيادات';
    protected static ?string $modelLabel = 'حجز عيادة';
    protected static ?string $pluralModelLabel = 'حجوزات العيادات';

    public static function getNavigationGroup(): ?string
    {
        return 'الحجوزات';
    }

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ClinicAppointmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClinicAppointmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    // ✅ صفحة واحدة فقط
    public static function getPages(): array
    {
        return [
            'index' => ListClinicAppointments::route('/'),
        ];
    }

    // ❌ بدون إضافة
    public static function canCreate(): bool
    {
        return false;
    }

    // ❌ بدون تعديل
    public static function canEdit($record): bool
    {
        return false;
    }

    // ❌ بدون حذف
    public static function canDelete($record): bool
    {
        return false;
    }
}