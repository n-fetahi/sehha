<?php

namespace App\Filament\Resources\HealthContents\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class HealthContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('title')
                    ->label('عنوان المحتوى')
                    ->required(),

                Textarea::make('content')
                    ->label('المحتوى')
                    ->required()
                    ->rows(8)
                    ->columnSpanFull(),

                FileUpload::make('image')
                    ->label('صورة المحتوى')
                    ->image()
                    ->directory('health-content')
                    ->columnSpanFull(),
            ]);
    }
}