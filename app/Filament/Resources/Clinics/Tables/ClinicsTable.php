<?php

namespace App\Filament\Resources\Clinics\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;

class ClinicsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // اسم المستخدم (من علاقة user)
                TextColumn::make('user.name')
                    ->label('اسم المستخدم')
                    ->searchable(),

                // اسم العيادة
                TextColumn::make('name')
                    ->label('اسم العيادة')
                    ->searchable(),

                // رقم المستخدم
                TextColumn::make('user.phone')
                    ->label('رقم المستخدم'),

                // رقم العيادة
                TextColumn::make('phone')
                    ->label('رقم العيادة'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
    ViewAction::make()
        ->label('عرض')
        ->icon('heroicon-o-eye'),
])
            ->toolbarActions([
                //
            ]);
    }
}