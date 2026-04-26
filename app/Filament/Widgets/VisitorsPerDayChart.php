<?php

namespace App\Filament\Widgets;

// use Carbon\Carbon;
// use Filament\Widgets\ChartWidget;
// use Illuminate\Support\Facades\DB;

// class VisitorsPerDayChart extends ChartWidget
// {
//     // 👈 في Filament v4 لازم تكون غير static
//     protected ?string $heading = 'زيارات آخر ٧ أيام';

//     protected function getData(): array
//     {
//         $days = collect(range(6, 0))->map(
//             fn (int $i) => Carbon::today()->subDays($i)
//         );

//         $labels = $days->map(
//             fn (Carbon $date) => $date->format('d/m')
//         );

//         $counts = $days->map(
//             fn (Carbon $date) => DB::table('visitors')
//                 ->whereDate('created_at', $date)
//                 ->count()
//         );

//         return [
//             'datasets' => [
//                 [
//                     'label' => 'عدد الزيارات',
//                     'data'  => $counts->toArray(),
//                 ],
//             ],
//             'labels' => $labels->toArray(),
//         ];
//     }

//     protected function getType(): string
//     {
//         // تقدر تغيّرها لـ 'line' لو تحب شكل خط
//         return 'bar';
//     }
// }
