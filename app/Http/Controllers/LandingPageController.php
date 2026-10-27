<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Portfolio;

class LandingPageController extends Controller
{
    /** نقشه اتصال اسلاگ → نام فایل blade (landings/*) */
    private const VIEW_MAP = [
        'wordpress-website-design' => 'wordpress',
        'web-development'          => 'webdev',
        'devops-services'          => 'devops',
        'about-us'                 => 'about',
    ];

    /** صفحاتی که نباید از مسیر عمومی دیده یا ایندکس شوند */
    private const HIDDEN = ['inv-8x2k9z'];

    /**
     * نام کامل view برای یک اسلاگ، یا null اگر وجود نداشته باشد / عمومی نباشد.
     */
    public static function viewFor(string $slug): ?string
    {
        if ($slug === 'cms' || in_array($slug, self::HIDDEN, true)) {
            return null;
        }

        $path = 'front.t-one.landings.'.(self::VIEW_MAP[$slug] ?? $slug);

        return view()->exists($path) ? $path : null;
    }

    /** اسلاگ همه لندینگ‌های عمومی که فایل blade دارند (برای sitemap) */
    public static function publicSlugs(): array
    {
        $slugs = [];
        foreach (glob(resource_path('views/front/t-one/landings/*.blade.php')) ?: [] as $file) {
            $name = basename($file, '.blade.php');
            $slug = array_search($name, self::VIEW_MAP, true) ?: $name;
            if (! in_array($slug, ['about-us','cms'], true) && ! in_array($slug, self::HIDDEN, true)) {
                $slugs[] = $slug;
            }
        }

        return $slugs;
    }

    public function show($slug)
    {
        $landing = LandingPage::query()->where('slug', $slug)->first();
        if ($landing && $landing->template === 'cms') {
            abort_unless($landing->is_active && $landing->published_at, 404);
            return view('front.t-one.landings.cms', ['landing'=>$landing, 'sections'=>$landing->sections ?? [], 'preview'=>false]);
        }
        $viewPath = self::viewFor($slug);
        if (! $viewPath) {
            abort(404);
        }

        // نمونه‌کارها (فقط برای صفحات خاص)
        $portfolios = collect();

        // دسته‌بندی نمونه‌کارها بر اساس slug (نه شناسه‌ی ثابت)؛ null یعنی آخرین نمونه‌کارها
        $categoryBySlug = [
            'wordpress-website-design' => 'wordpress',
            'web-development'          => 'web-platforms',
            'devops-services'          => 'devops',
            'seo'                      => null,
            'about-us'                 => null,
        ];

        if (array_key_exists($slug, $categoryBySlug)) {
            $catSlug = $categoryBySlug[$slug];
            $portfolios = Portfolio::where('status', 'published')
                ->when($catSlug, fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('categories.slug', $catSlug)))
                ->with('categories')
                ->latest()
                ->take(3)
                ->get();
        }

        return view($viewPath, compact('slug', 'portfolios'));
    }
}
