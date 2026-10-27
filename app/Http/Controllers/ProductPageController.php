<?php

namespace App\Http\Controllers;

use App\Models\ProductPage;

class ProductPageController extends Controller
{
    public function index()
    {
        $pages = ProductPage::published()->orderBy('sort_order')->orderBy('id')->get();

        return view('front.t-one.apps.index', compact('pages'));
    }

    public function show(string $slug)
    {
        $page = ProductPage::where('slug', $slug)->firstOrFail();

        $preview = false;
        if (! $page->isLive()) {
            // پیش‌نویس فقط برای کسانی که اجازه‌ی مدیریت دارند دیده می‌شود
            $user = auth()->user();
            abort_unless($user && $user->can('product-pages.manage'), 404);
            $preview = true;
        }

        return view('front.t-one.apps.show', compact('page', 'preview'));
    }
}
