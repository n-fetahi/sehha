<?php

namespace App\Filament\Resources\HealthContents;

use App\Filament\Resources\HealthContents\Pages\CreateHealthContent;
use App\Filament\Resources\HealthContents\Pages\EditHealthContent;
use App\Filament\Resources\HealthContents\Pages\ListHealthContents;
use App\Filament\Resources\HealthContents\Pages\ViewHealthContent;
use App\Filament\Resources\HealthContents\Schemas\HealthContentForm;
use App\Filament\Resources\HealthContents\Schemas\HealthContentInfolist;
use App\Filament\Resources\HealthContents\Tables\HealthContentsTable;
use App\Models\HealthContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HealthContentResource extends Resource
{
    protected static ?string $model = HealthContent::class;

    // ✅ أيقونة مناسبة
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    // ✅ تعريب
    protected static ?string $navigationLabel = 'المحتوى الطبي';
    protected static ?string $modelLabel = 'محتوى طبي';
    protected static ?string $pluralModelLabel = 'المحتوى الطبي';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return HealthContentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HealthContentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HealthContentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHealthContents::route('/'),
            'create' => CreateHealthContent::route('/create'),
            'view' => ViewHealthContent::route('/{record}'),
            'edit' => EditHealthContent::route('/{record}/edit'),
        ];
    }
}