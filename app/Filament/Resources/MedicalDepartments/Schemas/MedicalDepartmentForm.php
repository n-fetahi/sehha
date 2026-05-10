<?php

namespace App\Filament\Resources\MedicalDepartments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MedicalDepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
