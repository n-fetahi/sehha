<?php

namespace App\Filament\Resources\Labs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction;

class LabsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('اسم المستخدم'),

                TextColumn::make('name')
                    ->label('اسم المختبر'),

                TextColumn::make('user.phone')
                    ->label('رقم المستخدم'),

                TextColumn::make('phone')
                    ->label('رقم المختبر'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),
            ]);
    }
}
