<?php

namespace App\Providers\Filament;

use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')

            // ✅ مهم: يرجّع راوت login + logout الافتراضيين لفيلامنت
            ->login()          // /admin/login
            // ->passwordReset()   // لو تحب تضيف /admin/password-reset لاحقاً

            ->userMenu(false)

            // 🔹 اسم البراند حسب اللغة الحالية
            ->brandName(fn () => app()->getLocale() === 'ar' ? 'ظفار' : 'Dhofar')

            // 🔹 شعار واحد يستخدم في الهيدر وصفحة تسجيل الدخول
            // غيّر المسار حسب ملف الشعار الموجود فعلياً عندك
            // الوضع النهاري = شعار ملوّن
            ->brandLogo(asset('images/dhofar-logo-colored-small.svg'))

            // الوضع الليلي = الشعار الأبيض
            ->darkModeBrandLogo(asset('images/dhofar-logo-white-small.svg'))
            ->brandLogoHeight('3rem')

            // 🔹 الخط (اختياري)
            ->font(
                'Dhofar',
                url: asset('css/dhofar-font.css'),
                provider: LocalFontProvider::class,
            )

            // 🎨 الألوان
            ->colors([
                'primary' => [
                    50  => '237, 230, 240',
                    100 => '210, 191, 217',
                    200 => '182, 153, 194',
                    300 => '155, 115, 171',
                    400 => '128, 76, 148',
                    500 => '73, 0, 102',
                    600 => '58, 0, 82',
                    700 => '44, 0, 61',
                    800 => '29, 0, 41',
                    900 => '18, 0, 26',
                    950 => '10, 0, 15',
                ],
                'secondary' => [
                    50  => '248, 231, 241',
                    100 => '237, 194, 221',
                    200 => '226, 158, 200',
                    300 => '215, 121, 180',
                    400 => '204, 85, 159',
                    500 => '182, 12, 118',
                    600 => '146, 10, 94',
                    700 => '109, 7, 71',
                    800 => '73, 5, 47',
                    900 => '46, 3, 30',
                    950 => '25, 1, 17',
                ],
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')

            // لو ما تبغى Dashboard افتراضي خله فاضي
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])

            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
