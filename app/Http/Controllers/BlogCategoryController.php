<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    public function show(Category $category)
    {
        // فقط دسته‌های بلاگ معتبر
        abort_if(
            ! $category->is_active ||
            $category->type === 'portfolio',
            404
        );

        /**
         * 1️⃣ همه دسته‌بندی‌های بلاگ (برای سایدبار / منو)
         */
        $allCategories = Category::where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '!=', 'portfolio');
            })
            ->withCount([
                'posts as posts_count' => function ($q) {
                    $q->published();
                }
            ])
            ->orderBy('name')
            ->get();


        /**
         * 2️⃣ زیرمجموعه‌های دسته‌بندی جاری
         */
        $childrenCategories = $category->children()
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '!=', 'portfolio');
            })
            ->orderBy('name')
            ->get();

        /**
         * 3️⃣ پست‌های دسته جاری + زیرمجموعه‌ها
         */
        $categoryIds = $childrenCategories->pluck('id')->push($category->id);

        $posts = Post::whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        })
            ->published()
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(9);

        return view('front.t-one.posts.category', [
            'category'           => $category,
            'allCategories'      => $allCategories,
            'childrenCategories' => $childrenCategories,
            'posts'              => $posts,
        ]);
    }
}
