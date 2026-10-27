<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function(){
            Route::middleware(['web','auth','admin.access'])
                ->namespace('App\Http\Controllers\Admin')
                ->name('panel.')
                ->prefix('panel')
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.access' => \App\Http\Middleware\AdminAccess::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\CaptureUtm::class,
        ]);
    })->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->command('sitemap:generate')->dailyAt('03:30');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // پیام‌های فارسی برای درخواست‌های ajax (انقضای CSRF / تعداد درخواست زیاد)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, \Illuminate\Http\Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            $messages = [
                419 => 'نشست شما منقضی شده است. لطفاً صفحه را تازه‌سازی کنید و دوباره تلاش کنید.',
                429 => 'تعداد درخواست‌های شما زیاد است. لطفاً چند دقیقه بعد دوباره تلاش کنید.',
            ];

            if (! isset($messages[$e->getStatusCode()])) {
                return null;
            }

            return response()->json(['message' => $messages[$e->getStatusCode()]], $e->getStatusCode(), $e->getHeaders());
        });
    })->withCommands([
        App\Console\Commands\GenerateSitemap::class,
        App\Console\Commands\MakeAdmin::class,
    ])->create();
