<?php

namespace App\Console\Commands;

use App\Http\Controllers\LandingPageController;
use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductPage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL as UrlFacade;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Builds public/sitemap.xml with PUBLIC canonical pages only
 * (never utility routes such as captcha refresh, filemanager, panel, auth...).
 * Base URL = config('app.url') unless --url is given.
 */
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate
                            {--url= : Base URL override (default: config app.url)}
                            {--output= : Output file (default: public/sitemap.xml)}';

    protected $description = 'Generate sitemap.xml with public canonical pages only';

    public function handle(): int
    {
        $base = rtrim((string) ($this->option('url') ?: config('app.url')), '/');
        UrlFacade::forceRootUrl($base);
        if (str_starts_with($base, 'https://')) {
            UrlFacade::forceScheme('https');
        }

        if (preg_match('#//(localhost|127\.0\.0\.1)#', $base)) {
            $this->warn("Base URL is {$base}; set APP_URL (or pass --url=https://aron-soft.com) for production.");
        }

        try {
            $sitemap = Sitemap::create();
            $seen = [];
            $add = function (string $loc, $lastmod = null, string $freq = Url::CHANGE_FREQUENCY_WEEKLY, float $priority = 0.7) use ($sitemap, &$seen) {
                if (isset($seen[$loc])) {
                    return;
                }
                $seen[$loc] = true;
                $tag = Url::create($loc)->setChangeFrequency($freq)->setPriority($priority);
                if ($lastmod) {
                    $tag->setLastModificationDate($lastmod);
                }
                $sitemap->add($tag);
            };

            // ---- static public pages (explicit allow-list) ----
            $add(route('home'), now(), Url::CHANGE_FREQUENCY_DAILY, 1.0);
            $add(route('about'), null, Url::CHANGE_FREQUENCY_MONTHLY, 0.6);
            $add(route('blog.index'), now(), Url::CHANGE_FREQUENCY_DAILY, 0.8);
            $add(route('portfolio.index'), null, Url::CHANGE_FREQUENCY_WEEKLY, 0.8);
            $add(route('products.index'), null, Url::CHANGE_FREQUENCY_WEEKLY, 0.7);
            $add(route('contact'), null, Url::CHANGE_FREQUENCY_MONTHLY, 0.6);
            $add(route('order.create'), null, Url::CHANGE_FREQUENCY_MONTHLY, 0.7);

            // ---- landing pages (فایل‌های blade عمومی + ردیف‌های فعال دیتابیس) ----
            foreach (LandingPageController::publicSlugs() as $slug) {
                $add(route('landing.show', $slug), null, Url::CHANGE_FREQUENCY_MONTHLY, 0.8);
            }
            LandingPage::where('is_active', 1)->get()->each(function ($landing) use ($add) {
                if ($landing->slug === 'about-us' || ! LandingPageController::viewFor($landing->slug)) {
                    return;
                }
                $add(route('landing.show', $landing->slug), $landing->updated_at, Url::CHANGE_FREQUENCY_MONTHLY, 0.8);
            });

            // ---- blog ----
            Post::published()->whereNotNull('slug')->where('slug', '!=', '')
                ->orderBy('id')->cursor()
                ->each(fn ($post) => $add(route('blog.show', $post->slug), $post->updated_at, Url::CHANGE_FREQUENCY_MONTHLY, 0.7));

            Category::where('is_active', 1)
                ->where(fn ($q) => $q->whereNull('type')->orWhere('type', '!=', 'portfolio'))
                ->whereHas('posts', fn ($q) => $q->published())
                ->get()
                ->each(fn ($c) => $add(route('blog.category', $c->slug), $c->updated_at, Url::CHANGE_FREQUENCY_WEEKLY, 0.5));

            // ---- لندینگ محصولات آرون سافت ----
            $productPages = ProductPage::published()->where('noindex', false)->orderBy('sort_order')->get();
            if ($productPages->isNotEmpty()) {
                $add(route('product-pages.index'), $productPages->max('updated_at'), Url::CHANGE_FREQUENCY_WEEKLY, 0.8);
                $productPages->each(fn ($p) => $add($p->publicUrl(), $p->updated_at, Url::CHANGE_FREQUENCY_MONTHLY, 0.9));
            }

            // ---- portfolios ----
            Portfolio::where('status', 'published')->orderBy('id')->cursor()
                ->each(fn ($p) => $add(route('portfolio.show', $p->slug), $p->updated_at, Url::CHANGE_FREQUENCY_MONTHLY, 0.8));

            Category::where('type', 'portfolio')->where('is_active', 1)
                ->whereHas('portfolios', fn ($q) => $q->where('portfolios.status', 'published'))
                ->get()
                ->each(fn ($c) => $add(route('portfolio.category', $c->slug), $c->updated_at, Url::CHANGE_FREQUENCY_WEEKLY, 0.5));

            // ---- products ----
            Product::where('is_active', true)->orderBy('id')->cursor()
                ->each(fn ($p) => $add(route('products.show', $p->slug), $p->updated_at, Url::CHANGE_FREQUENCY_MONTHLY, 0.6));

            foreach (['wordpress_theme', 'wordpress_plugin', 'laravel_package', 'linux_script'] as $type) {
                if (Product::where('is_active', true)->where('type', $type)->exists()) {
                    $add(route('products.filter', $type), null, Url::CHANGE_FREQUENCY_WEEKLY, 0.5);
                }
            }

            $path = $this->option('output') ?: public_path('sitemap.xml');
            $sitemap->writeToFile($path);

            $this->info(count($seen)." URLs written to {$path}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Sitemap generation failed: '.$e->getMessage());
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
