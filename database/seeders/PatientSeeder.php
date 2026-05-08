<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            [
                'name' => 'Patient One',
                'phone' => '771123456',
                'gender' => 'male',
                'birth_date' => '1995-01-15',
                'blood_type' => 'O+',
                'height' => 175,
                'weight' => 72,
            ],
            [
                'name' => 'Patient Two',
                'phone' => '772123456',
                'gender' => 'female',
                'birth_date' => '1998-04-20',
                'blood_type' => 'A+',
                'height' => 162,
                'weight' => 58,
            ],
            [
                'name' => 'Patient Three',
                'phone' => '773123456',
                'gender' => 'male',
                'birth_date' => '1990-09-10',
                'blood_type' => 'B+',
                'height' => 180,
                'weight' => 84,
            ],
            [
                'name' => 'Patient Four',
                'phone' => '774123456',
                'gender' => 'female',
                'birth_date' => '2001-12-05',
                'blood_type' => 'AB+',
                'height' => 168,
                'weight' => 63,
            ],
            [
                'name' => 'Patient Five',
                'phone' => '775123456',
                'gender' => 'male',
                'birth_date' => '1987-07-25',
                'blood_type' => 'O-',
                'height' => 172,
                'weight' => 79,
            ],
        ];

        foreach ($patients as $patientData) {
            $user = User::updateOrCreate(
                ['phone' => $patientData['phone']],
                [
                    'name' => $patientData['name'],
                    'email' => null,
                    'password' => Hash::make('12345678'),
                    'user_type' => 'patient',
                    'gender' => $patientData['gender'],
                    'user_status' => 'approved',
                ]
            );

            Patient::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'birth_date' => $patientData['birth_date'],
                    'blood_type' => $patientData['blood_type'],
                    'height' => $patientData['height'],
                    'weight' => $patientData['weight'],
                ]
            );
        }
    }
}
