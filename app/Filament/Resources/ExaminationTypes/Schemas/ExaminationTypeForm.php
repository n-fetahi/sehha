<?php

namespace App\Filament\Resources\ExaminationTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExaminationTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم نوع الفحص')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}