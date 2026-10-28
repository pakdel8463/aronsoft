<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

/**
 * صفحه‌های صفحه‌ساز در سایت. از طریق Route::fallback صدا زده می‌شود، پس هر مسیری که سایت از قبل
 * دارد (/blog، /services/...، /panel، ...) همیشه اولویت دارد و فقط آدرس‌های تک‌بخشی آزاد به اینجا می‌رسند.
 */
class PageController extends Controller
{
    public function show(Request $request)
    {
        $slug = rawurldecode(trim($request->path(), '/'));
        abort_if($slug === '' || str_contains($slug, '/') || mb_strlen($slug) > 191, 404);

        $page = Page::published()->where('slug', $slug)->first();
        abort_unless($page, 404);

        if ($page->is_home) {
            return redirect()->route('home', status: 301);
        }

        return $this->render($page);
    }

    /** نمایش صفحه؛ $preview برای پیش‌نمایش در پنل (پیش‌نویس، قالب، iframe صفحه‌ساز). */
    public function render(Page $page, bool $preview = false, bool $embed = false)
    {
        $page->load('blocks');

        return response()->view('front.t-one.pages.show', [
            'page' => $page,
            'blocks' => $preview ? $page->blocks->filter(fn ($b) => $b->is_active && $b->isKnown())->values() : $page->renderableBlocks(),
            'preview' => $preview,
            'embed' => $embed,
        ])->withHeaders($preview ? ['X-Robots-Tag' => 'noindex, nofollow', 'Cache-Control' => 'no-store'] : []);
    }
}
