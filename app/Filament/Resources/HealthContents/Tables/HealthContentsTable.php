<?php

namespace App\Filament\Resources\HealthContents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HealthContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان المحتوى')
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('image')
                    ->label('الصورة')
                    ->square(),
            ])

            ->filters([
                //
            ])

            ->recordActions([
                ViewAction::make()
                    ->label('عرض'),

                EditAction::make()
                    ->label('تعديل'),

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