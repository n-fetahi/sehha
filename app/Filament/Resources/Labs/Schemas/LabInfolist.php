<?php

namespace App\Filament\Resources\Labs\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LabInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('phone'),
                TextEntry::make('medical_director')
                    ->placeholder('-'),
                TextEntry::make('location'),
                TextEntry::make('license_number'),
                TextEntry::make('license')
                    ->label('رخصة الترخيص')
                    ->badge()
                    ->url(fn($state) => $state ? asset('storage/' . $state) : null, shouldOpenInNewTab: true)
                    ->placeholder('-'),
                TextEntry::make('license_status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'pending' => 'قيد الانتظار',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => $state,
                    })
                    ->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected']),
                TextEntry::make('commercial_reg')
                    ->label('السجل التجاري')
                    ->badge()
                    ->url(fn($state) => $state ? asset('storage/' . $state) : null, shouldOpenInNewTab: true)
                    ->placeholder('-'),
                TextEntry::make('commercial_reg_status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'pending' => 'قيد الانتظار',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => $state,
                    })
                    ->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected']),
                TextEntry::make('rejection_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('user.name')
                    ->label('اسم المستخدم'),
                ImageEntry::make('profile_picture')
                    ->label('صورة المختبر')
                    ->disk('public')
                    ->placeholder('-'),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
