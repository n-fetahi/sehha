<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\ClinicSchedule;
use App\Models\ClinicScheduleDay;
use App\Models\MedicalDepartment;
use App\Models\User;
use App\Models\Weekday;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        $clinics = [
            [
                'doctor' => 'د. محمد الحيمي',
                'location' => 'صنعاء - شارع حدة',
                'experience' => 14,
                'start_time' => '08:00:00',
                'end_time' => '13:00:00',
                'session_duration' => 20,
                'follow_up_period' => 7,
                'booking_fee' => 5000,
                'is_available' => true,
                'weekday_indexes' => [0, 1, 2, 3],
                'rating' => 4.8,
            ],
            [
                'doctor' => 'د. أروى الشامي',
                'location' => 'عدن - كريتر',
                'experience' => 9,
                'start_time' => '16:00:00',
                'end_time' => '21:00:00',
                'session_duration' => 30,
                'follow_up_period' => 10,
                'booking_fee' => 6500,
                'is_available' => true,
                'weekday_indexes' => [1, 2, 4, 5],
                'rating' => 4.6,
            ],
            [
                'doctor' => 'د. عبد الله الصبري',
                'location' => 'تعز - الحوبان',
                'experience' => 18,
                'start_time' => '09:30:00',
                'end_time' => '15:30:00',
                'session_duration' => 25,
                'follow_up_period' => 14,
                'booking_fee' => 4500,
                'is_available' => false,
                'weekday_indexes' => [0, 3, 5],
                'rating' => 4.4,
            ],
            [
                'doctor' => 'د. نجلاء الأكوع',
                'location' => 'إب - شارع تعز',
                'experience' => 11,
                'start_time' => '07:30:00',
                'end_time' => '12:30:00',
                'session_duration' => 20,
                'follow_up_period' => 7,
                'booking_fee' => 4000,
                'is_available' => true,
                'weekday_indexes' => [0, 2, 4],
                'rating' => 4.5,
            ],
            [
                'doctor' => 'د. فارس باوزير',
                'location' => 'المكلا - فوة',
                'experience' => 16,
                'start_time' => '10:00:00',
                'end_time' => '17:00:00',
                'session_duration' => 30,
                'follow_up_period' => 21,
                'booking_fee' => 7000,
                'is_available' => false,
                'weekday_indexes' => [2, 3, 4, 6],
                'rating' => 4.7,
            ],
        ];

        $weekdays = Weekday::orderBy('id')->get()->values();
        $departments = MedicalDepartment::orderBy('id')->get();
        $phones = [
            '731123456',
            '732123456',
            '733123456',
            '734123456',
            '735123456',
            '736123456',
            '737123456',
            '738123456',
            '739123456',
            '730123456',
            '730223456',
            '730323456',
            '730423456',
            '730523456',
            '730623456',
        ];
        $phoneSequence = 1;

        foreach ($departments as $department) {
            for ($index = 0; $index < 3; $index++) {
                $clinicData = $clinics[($department->id + $index - 1) % count($clinics)];
                $phone = $phones[$phoneSequence - 1] ?? '73' . str_pad((string) ($phoneSequence + 10000), 7, '0', STR_PAD_LEFT);
                $licenseNumber = 'CLN-YE-' . str_pad((string) $phoneSequence, 3, '0', STR_PAD_LEFT);
                $clinicName = 'عيادة ' . $clinicData['doctor'] . ' - ' . $department->name;

                $user = User::updateOrCreate(
                    ['phone' => $phone],
                    [
                        'name' => $clinicName,
                        'email' => null,
                        'password' => Hash::make('12345678'),
                        'user_type' => 'clinic',
                        'gender' => str_contains($clinicData['doctor'], 'د. أروى') || str_contains($clinicData['doctor'], 'د. نجلاء') ? 'female' : 'male',
                        'user_status' => 'approved',
                    ]
                );

                $clinic = Clinic::updateOrCreate(
                    ['license_number' => $licenseNumber],
                    [
                        'name' => $clinicName,
                        'phone' => $phone,
                        'years_of_experience' => $clinicData['experience'],
                        'bio' => 'عيادة متخصصة في قسم ' . $department->name . ' تقدم خدمات كشف ومتابعة للمرضى.',
                        'location' => $clinicData['location'],
                        'license' => 'licenses/clinics/' . $licenseNumber . '.pdf',
                        'license_status' => 'approved',
                        'commercial_reg' => 'commercial-registers/clinics/' . $licenseNumber . '.pdf',
                        'commercial_reg_status' => 'approved',
                        'rejection_reason' => null,
                        'user_id' => $user->id,
                        'secretary_id' => null,
                        'medical_department_id' => $department->id,
                        'profile_picture' => null,
                        'rating' => $clinicData['rating'],
                    ]
                );

                $schedule = ClinicSchedule::firstOrNew(['clinic_id' => $clinic->id]);
                $schedule->forceFill([
                    'start_time' => $clinicData['start_time'],
                    'end_time' => $clinicData['end_time'],
                    'session_duration' => $clinicData['session_duration'],
                    'follow_up_period' => $clinicData['follow_up_period'],
                    'booking_fee' => $clinicData['booking_fee'] + ($index * 500),
                    'is_available' => $index === 2 ? ! $clinicData['is_available'] : $clinicData['is_available'],
                ])->save();

                ClinicScheduleDay::where('clinic_schedule_id', $schedule->id)->delete();

                foreach ($clinicData['weekday_indexes'] as $weekdayIndex) {
                    $weekday = $weekdays->get(($weekdayIndex + $index) % max($weekdays->count(), 1));

                    if ($weekday === null) {
                        continue;
                    }

                    ClinicScheduleDay::firstOrCreate([
                        'clinic_schedule_id' => $schedule->id,
                        'weekday_id' => $weekday->id,
                    ]);
                }

                $phoneSequence++;
            }
        }
    }
}
