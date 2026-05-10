<?php

namespace App\Filament\Resources\ClinicAppointments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ClinicAppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('clinic_id')
                    ->relationship('clinic', 'name')
                    ->required(),
                Select::make('patient_id')
                    ->relationship('patient', 'user.name')
                    ->required(),
                TextInput::make('previous_appointment_id')
                    ->numeric(),
                Select::make('wallet_id')
                    ->relationship('wallet', 'name'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                TextInput::make('type')
                    ->required()
                    ->default('consultation'),
                DatePicker::make('appointment_date')
                    ->required(),
                TimePicker::make('appointment_time')
                    ->required(),
                DatePicker::make('follow_up_date'),
                TextInput::make('follow_up_period')
                    ->numeric(),
                Textarea::make('diagnosis')
                    ->columnSpanFull(),
                Textarea::make('medications')
                    ->columnSpanFull(),
                TextInput::make('booking_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
