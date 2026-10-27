<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Category;
use App\Models\Portfolio;

class PortfolioController extends Controller
{
    /** /portfolios — همه نمونه‌کارهای منتشرشده */
    public function index()
    {
        return $this->category(null);
    }

    /** /portfolio/category/{slug} */
    public function category($slug = null)
    {
        $categories = Category::where('type', 'portfolio')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        if ($slug) {
            $currentCategory = Category::where('type', 'portfolio')
                ->where('slug', $slug)
                ->where('is_active', 1)
                ->firstOrFail();

            $query = $currentCategory->portfolios()->where('portfolios.status', 'published');
        } else {
            $currentCategory = null;
            $query = Portfolio::where('status', 'published');
        }

        $portfolios = $query->with('tags')->latest('portfolios.created_at')->paginate(9)->withQueryString();

        SeoHelper::set([
            'title' => $currentCategory ? 'نمونه‌کارهای '.$currentCategory->name : 'نمونه‌کارها',
            'description' => $currentCategory?->description
                ?: 'نمونه پروژه‌های طراحی سایت، فروشگاه اینترنتی، نرم‌افزار و زیرساخت که تیم آرون سافت اجرا کرده است.',
            'canonical' => $currentCategory
                ? route('portfolio.category', $currentCategory->slug)
                : route('portfolio.index'),
        ]);

        return view('front.t-one.portfolios.category', compact('categories', 'portfolios', 'currentCategory'));
    }

    public function show($slug)
    {
        $query = Portfolio::where('slug', $slug)->with(['categories', 'tags', 'images']);
        if (! $this->canPreview()) {
            $query->where('status', 'published');
        }
        $portfolio = $query->firstOrFail();

        $categoryIds = $portfolio->categories->pluck('id');
        $relatedPortfolios = Portfolio::where('status', 'published')
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds))
            ->where('id', '!=', $portfolio->id)
            ->take(3)
            ->get();

        $next = Portfolio::where('status', 'published')->where('id', '>', $portfolio->id)->orderBy('id', 'asc')->first();
        $previous = Portfolio::where('status', 'published')->where('id', '<', $portfolio->id)->orderBy('id', 'desc')->first();

        SeoHelper::set([
            'title' => $portfolio->seo_title ?: $portfolio->title,
            'description' => $portfolio->seo_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $portfolio->description), 160),
            'image' => $portfolio->featured_image ? asset($portfolio->featured_image) : null,
        ]);

        return view('front.t-one.portfolios.show', compact('portfolio', 'relatedPortfolios', 'next', 'previous'));
    }
}
