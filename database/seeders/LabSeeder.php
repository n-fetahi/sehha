<?php

namespace Database\Seeders;

use App\Models\ExaminationItem;
use App\Models\Lab;
use App\Models\LabExaminationItem;
use App\Models\LabSchedule;
use App\Models\LabScheduleDay;
use App\Models\User;
use App\Models\Weekday;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LabSeeder extends Seeder
{
    public function run(): void
    {
        $labs = [
            [
                'user_name' => 'مختبرات العولقي التخصصية',
                'phone' => '711123456',
                'medical_director' => 'د. أمين العولقي',
                'location' => 'صنعاء - شارع الزبيري',
                'license_number' => 'LAB-YE-SAN-001',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'is_available' => true,
                'weekday_indexes' => [0, 1, 2, 3, 4],
                'rating' => 4.7,
            ],
            [
                'user_name' => 'مختبرات النخبة الطبية',
                'phone' => '712123456',
                'medical_director' => 'د. سارة الحرازي',
                'location' => 'عدن - خور مكسر',
                'license_number' => 'LAB-YE-ADN-002',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
                'weekday_indexes' => [1, 2, 3, 4, 5],
                'rating' => 4.5,
            ],
            [
                'user_name' => 'مختبر ابن سينا التشخيصي',
                'phone' => '713123456',
                'medical_director' => 'د. خالد النجار',
                'location' => 'تعز - شارع جمال',
                'license_number' => 'LAB-YE-TAZ-003',
                'start_time' => '07:30:00',
                'end_time' => '14:30:00',
                'is_available' => false,
                'weekday_indexes' => [0, 2, 4],
                'rating' => 4.2,
            ],
            [
                'user_name' => 'مختبرات الشفاء الحديثة',
                'phone' => '714123456',
                'medical_director' => 'د. منى القباطي',
                'location' => 'إب - شارع العدين',
                'license_number' => 'LAB-YE-IBB-004',
                'start_time' => '10:00:00',
                'end_time' => '20:00:00',
                'is_available' => true,
                'weekday_indexes' => [0, 1, 3, 5],
                'rating' => 4.6,
            ],
            [
                'user_name' => 'مختبرات حضرموت الطبية',
                'phone' => '715123456',
                'medical_director' => 'د. عبد الرحمن باعباد',
                'location' => 'المكلا - شارع الستين',
                'license_number' => 'LAB-YE-MUK-005',
                'start_time' => '08:30:00',
                'end_time' => '15:30:00',
                'is_available' => false,
                'weekday_indexes' => [2, 3, 4, 6],
                'rating' => 4.3,
            ],
        ];

        $weekdays = Weekday::orderBy('id')->get()->values();
        $examinationItemIds = ExaminationItem::pluck('id');

        foreach ($labs as $labData) {
            $user = User::updateOrCreate(
                ['phone' => $labData['phone']],
                [
                    'name' => $labData['user_name'],
                    'email' => null,
                    'password' => Hash::make('12345678'),
                    'user_type' => 'lab',
                    'gender' => null,
                    'user_status' => 'approved',
                ]
            );

            $lab = Lab::updateOrCreate(
                ['license_number' => $labData['license_number']],
                [
                    'name' => $labData['user_name'],
                    'phone' => $labData['phone'],
                    'medical_director' => $labData['medical_director'],
                    'location' => $labData['location'],
                    'license' => 'licenses/labs/' . $labData['license_number'] . '.pdf',
                    'license_status' => 'approved',
                    'commercial_reg' => 'commercial-registers/labs/' . $labData['license_number'] . '.pdf',
                    'commercial_reg_status' => 'approved',
                    'rejection_reason' => null,
                    'user_id' => $user->id,
                    'profile_picture' => null,
                    'rating' => $labData['rating'],
                ]
            );

            $schedule = LabSchedule::updateOrCreate(
                ['lab_id' => $lab->id],
                [
                    'start_time' => $labData['start_time'],
                    'end_time' => $labData['end_time'],
                    'is_available' => $labData['is_available'],
                ]
            );

            LabScheduleDay::where('lab_schedule_id', $schedule->id)->delete();

            foreach ($labData['weekday_indexes'] as $weekdayIndex) {
                $weekday = $weekdays->get($weekdayIndex);

                if ($weekday === null) {
                    continue;
                }

                LabScheduleDay::firstOrCreate([
                    'lab_schedule_id' => $schedule->id,
                    'weekday_id' => $weekday->id,
                ]);
            }

            foreach ($examinationItemIds as $examinationItemId) {
                LabExaminationItem::firstOrCreate([
                    'lab_id' => $lab->id,
                    'examination_item_id' => $examinationItemId,
                ]);
            }
        }
    }
}
