<?php

namespace App\Filament\Resources\ExaminationTypes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    // ✅ اسم العلاقة الصحيح الموجود في ExaminationType
    protected static string $relationship = 'items';

    // ✅ اسم التبويب
    protected static ?string $title = 'الفحوصات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الفحص')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')

            ->columns([
                TextColumn::make('name')
                    ->label('اسم الفحص')
                    ->searchable()
                    ->sortable(),
            ])

            ->filters([
                //
            ])

            // ✅ زر إضافة فحص
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة فحص'),
            ])

            // ✅ تعديل + حذف فقط
            ->recordActions([
                EditAction::make()
                    ->label('تعديل'),

                DeleteAction::make()
                    ->label('حذف'),
            ])

            // ✅ حذف جماعي
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ]);
    }
}