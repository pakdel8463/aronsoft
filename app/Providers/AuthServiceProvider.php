<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Ticket::class => \App\Policies\TicketPolicy::class,
    ];

    public function boot()
    {
        // اگر کاربر سوپریوزر باشد → همه Gate ها true می‌شوند
        Gate::before(function ($user) {
            return $user->is_superuser == 1 ? true : null;
        });

        // ساخت Gate داینامیک از دیتابیس
        // (داخل try تا اگر دیتابیس در دسترس نبود — مثلاً هنگام نصب/key:generate یا migrate اولیه — برنامه از کار نیفتد)
        try {
            if (Schema::hasTable('permissions')) {
                Permission::all()->each(function ($permission) {
                    Gate::define($permission->slug, function ($user) use ($permission) {
                        return $user->hasPermission($permission->slug);
                    });
                });
            }
        } catch (\Throwable $e) {
            // دیتابیس یا جدول هنوز آماده نیست
        }
    }
}
