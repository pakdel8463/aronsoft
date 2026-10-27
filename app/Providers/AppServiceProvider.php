<?php

namespace App\Providers;

use App\Helpers\DynamicRouteHelper;
use App\Models\DynamicRoute;
use App\Models\Menu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            // دریافت همه منوها و ایندکس کردن آنها بر اساس فیلد position
            // نتیجه: ['header' => MenuObject, 'footer' => MenuObject, ...]
            // اگر دیتابیس در دسترس نباشد (مثلاً صفحه خطای ۵۰۰/۵۰۳) نباید خود صفحه خطا خراب شود
            // (و فقط یک‌بار در هر درخواست کوئری می‌زنیم، نه به‌ازای هر partial)
            static $menus = null;
            if ($menus === null) {
                try {
                    $menus = Menu::all()->keyBy('position');
                } catch (\Throwable $e) {
                    $menus = collect();
                }
            }
            $view->with('globalMenus', $menus);
        });

        Paginator::defaultView('vendor.pagination.tech');
    }
}
