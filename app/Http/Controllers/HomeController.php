<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Portfolio;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        // اگر در صفحه‌ساز صفحه‌ای «صفحه اصلی» شده باشد، به‌جای صفحه‌ی ثابت زیر نمایش داده می‌شود.
        if ($page = Page::home()) {
            return app(PageController::class)->render($page);
        }

        $latestProjects = Portfolio::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $latestPosts = Post::published()
            ->with('categories')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        return view('front.t-one.home', compact('latestProjects', 'latestPosts'));
    }
}
