<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            // الشرط الذي يحدد إذا كان المدير موجود
            ['email' => 'admin@sehha.com'], // إذا وجد هذا البريد يحدثه، إذا لا ينشئه
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('admin123'), // غيّرها لما تحب
                'user_type' => 'admin',
                'phone' => '776604262',
                'gender' => null, // يمكن تركها فارغة
                'user_status' => 'approved'


            ]
        );
    }
}
