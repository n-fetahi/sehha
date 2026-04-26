<?php

namespace App\Filament\Widgets;

// use Carbon\Carbon;
// use Filament\Widgets\StatsOverviewWidget;
// use Filament\Widgets\StatsOverviewWidget\Stat;
// use Illuminate\Support\Facades\DB;

// class VisitorsOverview extends StatsOverviewWidget
// {
//     protected function getStats(): array
//     {
//         $today       = Carbon::today();
//         $startOfMonth = $today->copy()->startOfMonth();
//         $endOfMonth   = $today->copy()->endOfMonth();

//         // ⚠️ يفترض أن اسم الجدول "visitors" وعمود IP اسمه "ip"
//         // عدّل الأسماء هنا لو كانت مختلفة في المايجريشن.
//         $totalVisits = DB::table('visitors')->count();

//         $uniqueVisits = DB::table('visitors')
//             ->distinct('ip')
//             ->count('ip');

//         $todayVisits = DB::table('visitors')
//             ->whereDate('created_at', $today)
//             ->count();

//         $monthVisits = DB::table('visitors')
//             ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
//             ->count();

//         // بيانات صغيرة للرسم المصغّر داخل أول كرت (Sparkline)
//         $last7Days = collect(range(6, 0))->map(
//             fn (int $i) => Carbon::today()->subDays($i)
//         );

//         $last7DaysCounts = $last7Days->map(
//             fn (Carbon $date) => DB::table('visitors')
//                 ->whereDate('created_at', $date)
//                 ->count()
//         );

//         return [
//             Stat::make('إجمالي الزيارات', number_format($totalVisits))
//                 ->description('منذ بدء التتبع')
//                 ->icon('heroicon-o-globe-alt')
//                 ->color('primary')
//                 ->chart($last7DaysCounts->toArray()),

//             Stat::make('الزوار الفريدون', number_format($uniqueVisits))
//                 ->description('كل عنوان IP يُحتسب مرة واحدة')
//                 ->icon('heroicon-o-users')
//                 ->color('secondary'),

//             Stat::make('زيارات اليوم', number_format($todayVisits))
//                 ->description('اليوم ' . $today->format('Y-m-d'))
//                 ->icon('heroicon-o-sun')
//                 ->color('success'),

//             Stat::make('زيارات هذا الشهر', number_format($monthVisits))
//                 ->description('منذ ' . $startOfMonth->format('Y-m-d'))
//                 ->icon('heroicon-o-chart-bar')
//                 ->color('warning'),
//         ];
//     }
// }
