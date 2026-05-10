<?php

namespace App\Filament\Resources\Patients\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المريض'),

                TextColumn::make('phone')
                    ->label('رقم الهاتف'),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
            ])
            ->bulkActions([]); // حذفنا العمليات الجماعية
    }
}