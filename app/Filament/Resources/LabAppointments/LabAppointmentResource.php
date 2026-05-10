<?php

namespace App\Filament\Resources\LabAppointments;

use App\Filament\Resources\LabAppointments\Pages\ListLabAppointments;
use App\Filament\Resources\LabAppointments\Schemas\LabAppointmentForm;
use App\Filament\Resources\LabAppointments\Tables\LabAppointmentsTable;
use App\Models\LabAppointment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LabAppointmentResource extends Resource
{
    protected static ?string $model = LabAppointment::class;

    // ✅ أيقونة مناسبة للحجوزات (وقت / موعد)
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    // ✅ تعريب
    protected static ?string $navigationLabel = 'حجوزات الفحوصات';
    protected static ?string $modelLabel = 'حجز فحص';
    protected static ?string $pluralModelLabel = 'حجوزات الفحوصات';

    public static function getNavigationGroup(): ?string
    {
        return 'الحجوزات';
    }

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return LabAppointmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabAppointmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLabAppointments::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}