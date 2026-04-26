<?php
// database/seeders/WeekdaySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Weekday;
use Illuminate\Support\Carbon;

class WeekdaySeeder extends Seeder
{
    public function run(): void
    {
        $days = [
            'السبت'  ,  // السبت
            'الأحد'    ,  // الأحد
            'الإثنين'   ,  // الإثنين
            'الثلاثاء'  ,  // الثلاثاء
            'الأربعاء',  // الأربعاء
            'الخميس' ,  // الخميس
            'الجمعة'   ,  // الجمعة
        ];

        foreach ($days as $day) {
            Weekday::updateOrCreate([
                'name' => $day
            ]);
        }
    }
}
