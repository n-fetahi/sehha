<?php

namespace App\Filament\Resources\LabAppointments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LabAppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lab_id')
                    ->relationship('lab', 'name')
                    ->required(),
                Select::make('patient_id')
                    ->relationship('patient', 'user.name')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('booked'),
                TextInput::make('result'),
            ]);
    }
}
