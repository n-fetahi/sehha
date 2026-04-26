<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalDepartment;

class MedicalDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'باطنية',
            'قلب',
            'أطفال',
            'جلدية',
            'عظام'
        ];

        foreach ($departments as $dept) {
            MedicalDepartment::create([
                'name' => $dept
            ]);
        }
    }
}