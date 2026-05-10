<?php

namespace App\Filament\Resources\Labs;

use App\Filament\Resources\Labs\Pages\CreateLab;
use App\Filament\Resources\Labs\Pages\EditLab;
use App\Filament\Resources\Labs\Pages\ListLabs;
use App\Filament\Resources\Labs\Pages\ViewLab;
use App\Filament\Resources\Labs\Schemas\LabForm;
use App\Filament\Resources\Labs\Schemas\LabInfolist;
use App\Filament\Resources\Labs\Tables\LabsTable;
use App\Models\Lab;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LabResource extends Resource
{
    protected static ?string $model = Lab::class;

    // ✅ أيقونة مختلفة تمامًا (تحليل / فحص)
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    // ✅ تعريب القائمة
    protected static ?string $navigationLabel = 'المختبرات';
    protected static ?string $modelLabel = 'مختبر';
    protected static ?string $pluralModelLabel = 'المختبرات';

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المستخدمين';
    }

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return LabForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LabInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabsTable::configure($table);
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
            'index' => ListLabs::route('/'),
            'create' => CreateLab::route('/create'),
            'view' => ViewLab::route('/{record}'),
            'edit' => EditLab::route('/{record}/edit'),
        ];
    }
}