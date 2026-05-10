<?php

namespace App\Filament\Resources\Patients;

use App\Filament\Resources\Patients\Pages\ListPatients;
use App\Filament\Resources\Patients\Pages\ViewPatient;
use App\Filament\Resources\Patients\Schemas\PatientInfolist;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class PatientResource extends Resource
{
    protected static ?string $model = User::class;

    // ✅ أيقونة مناسبة للمرضى
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    // ✅ تعريب القائمة
    protected static ?string $navigationLabel = 'المرضى';
    protected static ?string $modelLabel = 'مريض';
    protected static ?string $pluralModelLabel = 'المرضى';

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المستخدمين';
    }

    protected static ?int $navigationSort = 4;

    // فلترة المرضى فقط
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_type', 'patient')
            ->with('patient');
    }

    public static function infolist(Schema $schema): Schema
    {
        return PatientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('phone')->label('رقم الهاتف'),
                TextColumn::make('patient.gender')->label('الجنس'),
            ])
            ->actions([
                ViewAction::make()->label('عرض'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPatients::route('/'),
            'view'  => ViewPatient::route('/{record}'),
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
}