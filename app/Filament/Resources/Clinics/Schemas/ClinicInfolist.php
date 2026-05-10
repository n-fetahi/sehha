<?php

namespace App\Filament\Resources\Clinics\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ClinicInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('phone'),
                TextEntry::make('years_of_experience')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('bio')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
                TextEntry::make('secretary.name')
                    ->label('اسم السكرتير')
                    ->placeholder('-'),
                TextEntry::make('department.name')
                    ->label('القسم الطبي'),
                ImageEntry::make('profile_picture')
                    ->label('صورة العيادة')
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
