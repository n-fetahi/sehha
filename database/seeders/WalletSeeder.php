<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wallet;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        // قائمة المحافظ مع أسماء الصور (ستضعها أنت في المجلد المناسب)
       $wallets = [
            [
                'name' => 'جيب',
                'image' => 'wallets/jeeb.jpg',
            ],
            [
                'name' => 'ون كاش',
                'image' => 'wallets/onecash.jpg',  // صحح المسار هنا
            ],
            [
                'name' => 'جوالي',
                'image' => 'wallets/jawali.jpg',
            ],
        ];

        foreach ($wallets as $wallet) {
            Wallet::create($wallet);
        }
    }
}
