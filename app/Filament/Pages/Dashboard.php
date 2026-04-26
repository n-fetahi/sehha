<?php

namespace App\Filament\Pages;

use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;


class Dashboard extends BaseDashboard
{
    public static function getNavigationLabel(): string
    {
        return 'لوحة التحكم';
    }

    public  function getTitle(): string
    {
        return 'لوحة التحكم';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-presentation-chart-line';
    }

    public static function getNavigationSort(): ?int
    {
        return -2;
    }

    /**
     * الودجات الظاهرة في صفحة لوحة التحكم
     */


    /**
     * توزيع الأعمدة للودجات (12 عمود = Grid كامل)
     */
    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    /**
     * الأزرار في الهيدر (أعلى الصفحة على اليمين)
     * زر تبديل الثيم + زر تسجيل الخروج
     */
    public function getHeaderActions(): array
    {
        return [
            // زر تبديل الثيم (ليلي / نهاري) – زر واحد فقط
            Actions\Action::make('toggleTheme')
                ->label('تبديل الثيم')
                ->icon('heroicon-o-moon')
                ->extraAttributes([
                    // نستخدم Alpine الموجود مع فلامنت
                    'x-data' => "{
                        mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        toggle() {
                            this.mode = this.mode === 'dark' ? 'light' : 'dark';

                            if (this.mode === 'dark') {
                                document.documentElement.classList.add('dark');
                                localStorage.setItem('theme', 'dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.setItem('theme', 'light');
                            }
                        }
                    }",
                    '@click' => 'toggle()',
                ]),

            // زر تسجيل الخروج
            Actions\Action::make('logout')
                ->label('تسجيل الخروج')
                ->color('danger')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->action(function () {
                    Filament::auth()->logout();

                    // نرجعه لصفحة تسجيل الدخول للوحة الإدارة
                    return redirect()->route('filament.admin.auth.login');
                }),
        ];
    }
}
