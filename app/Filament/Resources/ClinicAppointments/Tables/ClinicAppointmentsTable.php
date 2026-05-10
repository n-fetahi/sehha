<?php

namespace App\Filament\Resources\ClinicAppointments\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ClinicAppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // اسم المريض
                TextColumn::make('patient.user.name')
                    ->label('اسم المريض'),

                // اسم العيادة
                TextColumn::make('clinic.name')
                    ->label('اسم العيادة'),

                // تاريخ الحجز
                TextColumn::make('appointment_date')
                    ->label('تاريخ الحجز'),

                // وقت الحجز
                TextColumn::make('appointment_time')
                    ->label('توقيت الحجز'),

                // نوع الحجز (بالعربي)
                TextColumn::make('type_name')
                    ->label('نوع الحجز'),

                // حالة الحجز (بالعربي)
                TextColumn::make('status_name')
                    ->label('حالة الحجز'),
            ]);
    }
}