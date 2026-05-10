<?php

namespace App\Filament\Resources\ExaminationTypes\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExaminationTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم نوع الفحص')
                    ->searchable()
                    ->sortable(),
            ])

            ->filters([
                //
            ])

            ->recordActions([

                // ✅ زر عرض الفحوصات
                Action::make('view_items')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => "/admin/examination-types/{$record->id}"),

                // ✅ تعديل النوع
                EditAction::make()
                    ->label('تعديل'),

                // ✅ حذف النوع
                DeleteAction::make()
                    ->label('حذف'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ]);
    }
}