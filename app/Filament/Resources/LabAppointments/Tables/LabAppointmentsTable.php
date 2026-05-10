<?php

namespace App\Filament\Resources\LabAppointments\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class LabAppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // اسم المريض
                TextColumn::make('patient.user.name')
                    ->label('اسم المريض'),

                // اسم المختبر
                TextColumn::make('lab.name')
                    ->label('اسم المختبر'),

                // حالة الحجز (بالعربي)
                TextColumn::make('status_name')
                    ->label('حالة الحجز'),
            ]);
    }
}