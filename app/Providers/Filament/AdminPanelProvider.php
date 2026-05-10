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
            ->brandName(fn () => app()->getLocale() === 'ar' ? 'صحة' : 'Sehha')

            // 🔹 شعار واحد يستخدم في الهيدر وصفحة تسجيل الدخول
            // غيّر المسار حسب ملف الشعار الموجود فعلياً عندك
            // الوضع النهاري = شعار ملوّن
            ->brandLogo(asset('images/logo.png'))

            // الوضع الليلي
            ->darkModeBrandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3rem')

            // 🔹 الخط (اختياري)
            ->font(
                'Sehha',
                url: asset('css/dhofar-font.css'),
                provider: LocalFontProvider::class,
            )

            // 🎨 الألوان
            ->colors([
                'primary' => [
                    50  => '227, 242, 253',
                    100 => '187, 222, 251',
                    200 => '144, 202, 249',
                    300 => '100, 181, 246',
                    400 => '66, 165, 245',
                    500 => '33, 150, 243',
                    600 => '30, 136, 229',
                    700 => '25, 118, 210',
                    800 => '21, 101, 192',
                    900 => '13, 71, 161',
                    950 => '6, 40, 100',
                ],
                'secondary' => [
                    50  => '236, 239, 241',
                    100 => '207, 216, 220',
                    200 => '176, 190, 197',
                    300 => '144, 164, 174',
                    400 => '120, 144, 156',
                    500 => '96, 125, 139',
                    600 => '84, 110, 122',
                    700 => '69, 90, 100',
                    800 => '55, 71, 79',
                    900 => '38, 50, 56',
                    950 => '20, 30, 35',
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
