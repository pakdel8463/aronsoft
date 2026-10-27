<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // کاربر باید لاگین باشد
        if (!$user) {
            return redirect()->route('login');
        }

        // اگر superuser باشد → اجازه کامل
        if ($user->is_superuser == 1) {
            return $next($request);
        }

        // اگر staff باشد → اجازه دارد
        if ($user->is_staff == 1) {
            return $next($request);
        }

        // در غیر اینصورت → ممنوع
        return redirect()->route('home');
    }
}
