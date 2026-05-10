<?php

namespace App\Filament\Resources\Labs\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LabForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('medical_director'),
                TextInput::make('location')
                    ->required(),
                TextInput::make('license_number')
                    ->required(),
                FileUpload::make('license')
                    ->label('رخصة الترخيص')
                    ->directory('licenses')
                    ->disk('public')
                    ->downloadable()
                    ->openable()
                    ->required(),
                Hidden::make('license_status')
                    ->default('pending'),
                FileUpload::make('commercial_reg')
                    ->label('السجل التجاري')
                    ->directory('commercial_regs')
                    ->disk('public')
                    ->downloadable()
                    ->openable()
                    ->required(),
                Hidden::make('commercial_reg_status')
                    ->default('pending'),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                FileUpload::make('profile_picture')
                    ->label('صورة المختبر')
                    ->image()
                    ->directory('labs')
                    ->disk('public'),
                TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
