<?php

namespace Database\Seeders;

use App\Models\HealthContent;
use Illuminate\Database\Seeder;

class HealthContentSeeder extends Seeder
{
    public function run(): void
    {
        $contents = [
            [
                'title' => 'أهمية شرب الماء يوميا',
                'content' => 'يساعد شرب كمية كافية من الماء على تحسين وظائف الجسم، دعم التركيز، والمحافظة على نشاط الدورة الدموية.',
                'image' => 'health-content/water.jpg',
            ],
            [
                'title' => 'النوم الصحي',
                'content' => 'الحصول على نوم منتظم وكاف يساعد الجسم على التعافي، ويحسن المزاج والقدرة على التركيز خلال اليوم.',
                'image' => 'health-content/sleep.jpg',
            ],
            [
                'title' => 'الغذاء المتوازن',
                'content' => 'تناول وجبات متنوعة تحتوي على الخضار، الفواكه، البروتينات، والحبوب الكاملة يدعم صحة الجسم ويقلل مخاطر الأمراض.',
                'image' => 'health-content/nutrition.jpg',
            ],
            [
                'title' => 'النشاط البدني',
                'content' => 'ممارسة المشي أو أي نشاط بدني مناسب بشكل منتظم تساعد على تقوية القلب والعضلات وتحسين اللياقة العامة.',
                'image' => 'health-content/activity.jpg',
            ],
            [
                'title' => 'الفحوصات الدورية',
                'content' => 'إجراء الفحوصات الطبية الدورية يساعد على اكتشاف المشكلات الصحية مبكرا ورفع فرصة الوقاية والعلاج.',
                'image' => 'health-content/checkup.jpg',
            ],
        ];

        foreach ($contents as $content) {
            HealthContent::updateOrCreate(
                ['title' => $content['title']],
                $content
            );
        }
    }
}
