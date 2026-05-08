<?php

namespace Database\Seeders;

use App\Models\MedicalDepartment;
use Illuminate\Database\Seeder;

class MedicalDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'باطنية',
            'قلب',
            'أطفال',
            'جلدية',
            'عظام',
        ];

        foreach ($departments as $department) {
            MedicalDepartment::updateOrCreate(['name' => $department]);
        }
    }
}
