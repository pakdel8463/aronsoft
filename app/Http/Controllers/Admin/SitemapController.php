<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class SitemapController extends Controller
{
    /** POST /panel/sitemap/generate — بازسازی sitemap.xml (فقط ادمین) */
    public function generate()
    {
        $code = Artisan::call('sitemap:generate');

        return back()->with(
            $code === 0 ? 'success' : 'error',
            $code === 0 ? 'نقشه سایت با موفقیت بازسازی شد.' : 'بازسازی نقشه سایت با خطا مواجه شد؛ لاگ را بررسی کنید.'
        );
    }
}
