<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExaminationType;
use App\Models\ExaminationItem;

class ExaminationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء نوع الفحص الأول: فحوصات الدم
        $bloodType = ExaminationType::create([
            'name' => 'فحوصات الدم',
        ]);

        // عناصر فحوصات الدم
        ExaminationItem::create([
            'name' => 'Complete Blood Count (CBC)',
            'examination_type_id' => $bloodType->id,
        ]);
        ExaminationItem::create([
            'name' => 'Hemoglobin (Hb)',
            'examination_type_id' => $bloodType->id,
        ]);
        ExaminationItem::create([
            'name' => 'Blood Glucose',
            'examination_type_id' => $bloodType->id,
        ]);

        // 2. إنشاء نوع الفحص الثاني: فحوصات السكري
        $diabetesType = ExaminationType::create([
            'name' => 'فحوصات السكري',
        ]);

        // عناصر فحوصات السكري
        ExaminationItem::create([
            'name' => 'HbA1c (Glycated Hemoglobin)',
            'examination_type_id' => $diabetesType->id,
        ]);
        ExaminationItem::create([
            'name' => 'Fasting Blood Sugar',
            'examination_type_id' => $diabetesType->id,
        ]);
        ExaminationItem::create([
            'name' => 'Glucose Tolerance Test',
            'examination_type_id' => $diabetesType->id,
        ]);
    }
}
