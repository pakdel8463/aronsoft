<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * نمایش لیست کل محصولات
     */
    public function index()
    {
        $products = Product::where('is_active', true)
            ->latest()
            ->paginate(9);

        return view('front.t-one.products.index', compact('products'));
    }

    /**
     * فیلتر محصولات بر اساس نوع (قالب وردپرس، پکیج لاراول و ...)
     */
    public function filterByType($type)
    {
        // چک کردن اینکه تایپ ورودی معتبر باشد
        $validTypes = ['wordpress_theme', 'wordpress_plugin', 'laravel_package', 'linux_script'];

        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $products = Product::where('is_active', true)
            ->where('type', $type)
            ->latest()
            ->paginate(9);

        return view('front.t-one.products.index', compact('products', 'type'));
    }

    /**
     * نمایش صفحه تکی محصول
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // محصولات مرتبط (هم‌نوع) برای نمایش در پایین صفحه (اختیاری)
        $relatedProducts = Product::where('type', $product->type)
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->take(3)
            ->get();

        SeoHelper::set([
            'title'       => $product->seo_title ?: $product->title,
            'description' => $product->seo_description,
            'image'       => $product->featured_image ? asset($product->featured_image) : null,
        ]);

        return view('front.t-one.products.single', compact('product', 'relatedProducts'));
    }
}
