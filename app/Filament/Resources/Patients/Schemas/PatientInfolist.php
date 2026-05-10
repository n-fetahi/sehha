<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PatientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('اسم المريض'),
                TextEntry::make('phone')
                    ->label('رقم الهاتف'),
                TextEntry::make('email')
                    ->label('البريد الإلكتروني')
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->label('الجنس')
                    ->formatStateUsing(fn(?string $state) => match($state) {
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                        default => '-',
                    }),
                TextEntry::make('user_status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'pending' => 'قيد الانتظار',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        'restricted' => 'مقيد',
                        default => $state,
                    })
                    ->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected', 'gray' => 'restricted']),
                TextEntry::make('patient.birth_date')
                    ->label('تاريخ الميلاد')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('patient.blood_type')
                    ->label('فصيلة الدم')
                    ->placeholder('-'),
                TextEntry::make('patient.height')
                    ->label('الطول (سم)')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('patient.weight')
                    ->label('الوزن (كجم)')
                    ->numeric()
                    ->placeholder('-'),
                ImageEntry::make('patient.profile_picture')
                    ->label('الصورة الشخصية')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
