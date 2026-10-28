<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PageController as PublicPageController;
use App\Models\Page;
use App\Pages\PageBuilder;
use App\Support\ProductPageSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** صفحه‌ها و قالب‌های صفحه‌ساز: فهرست، ساخت (از قالب)، تنظیمات و سئو، انتشار، صفحه‌ی اصلی، تکثیر و حذف. */
class PageController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'templates' ? 'templates' : 'pages';
        $q = trim((string) $request->query('q', ''));

        $pages = Page::query()
            ->where('is_template', $tab === 'templates')
            ->withCount('blocks')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%")))
            ->orderByDesc('is_home')->orderByDesc('updated_at')
            ->paginate(20)->withQueryString();

        return view('admin.dash.pages.index', [
            'pages' => $pages,
            'tab' => $tab,
            'q' => $q,
            'counts' => ['pages' => Page::pages()->count(), 'templates' => Page::templates()->count()],
            'templates' => Page::templates()->withCount('blocks')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $asTemplate = $request->boolean('as_template');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'integer', Rule::exists('pages', 'id')],
        ], [], ['title' => 'عنوان', 'slug' => 'آدرس', 'from' => 'قالب']);

        // قالب آدرس عمومی ندارد؛ slug فقط باید یکتا باشد
        $slug = $asTemplate ? PageBuilder::suggestSlug('template-'.$data['title']) : $this->validSlug($data['slug'] ?? '', $data['title']);
        $from = ! empty($data['from']) ? Page::find($data['from']) : null;
        $page = PageBuilder::createPage($data['title'], $slug, $from, $asTemplate, $request->user()?->id);

        return redirect()->route('panel.pages.builder', $page)
            ->with('success', $asTemplate ? 'قالب ساخته شد؛ بلوک‌هایش را ویرایش کنید.' : 'صفحه ساخته شد. بعد از تکمیل، از «تنظیمات» منتشرش کنید.');
    }

    public function edit(Page $page)
    {
        return view('admin.dash.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:400'],
            'og_image' => ['nullable', 'string', 'max:500', ProductPageSchema::imageRule()],
            'noindex' => ['nullable', 'boolean'],
        ], [], [
            'title' => 'عنوان', 'slug' => 'آدرس', 'meta_title' => 'عنوان سئو',
            'meta_description' => 'توضیح سئو', 'og_image' => 'تصویر اشتراک‌گذاری',
        ]);

        $slug = $page->is_template ? $page->slug : $this->validSlug($data['slug'], $data['title'], $page);

        $page->update([
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $page->is_template ? ($data['description'] ?? null) : $page->description,
            'is_active' => $page->is_template ? false : $request->boolean('is_active'),
            'settings' => array_merge((array) $page->settings, [
                'meta_title' => trim((string) ($data['meta_title'] ?? '')),
                'meta_description' => trim((string) ($data['meta_description'] ?? '')),
                'og_image' => trim((string) ($data['og_image'] ?? '')),
                'noindex' => $request->boolean('noindex'),
            ]),
        ]);

        return back()->with('success', 'تنظیمات صفحه ذخیره شد.');
    }

    public function destroy(Page $page)
    {
        $title = $page->title;
        $tab = $page->is_template ? 'templates' : null;
        $page->delete();

        return redirect()->route('panel.pages.index', array_filter(['tab' => $tab]))->with('success', "«{$title}» حذف شد.");
    }

    public function duplicate(Request $request, Page $page)
    {
        $title = $page->title.' (کپی)';
        $slug = $page->is_template ? PageBuilder::suggestSlug('template-'.$page->slug.'-copy') : PageBuilder::suggestSlug($page->slug.'-copy');
        $copy = PageBuilder::createPage($title, $slug, $page, $page->is_template, $request->user()?->id);

        return redirect()->route('panel.pages.builder', $copy)->with('success', 'از «'.$page->title.'» یک کپی ساخته شد.');
    }

    /** ذخیره‌ی یک صفحه به‌عنوان قالب (کپی بلوک‌ها؛ صفحه‌ی اصلی دست نمی‌خورد). */
    public function saveAsTemplate(Request $request, Page $page)
    {
        abort_if($page->is_template, 404);
        $tpl = PageBuilder::createPage('قالب: '.$page->title, PageBuilder::suggestSlug('template-'.$page->slug), $page, true, $request->user()?->id);

        return redirect()->route('panel.pages.index', ['tab' => 'templates'])->with('success', "قالب «{$tpl->title}» ساخته شد.");
    }

    public function togglePublish(Page $page)
    {
        abort_if($page->is_template, 404);
        $page->update(['is_active' => ! $page->is_active]);

        return back()->with('success', $page->is_active ? 'صفحه منتشر شد.' : 'صفحه به پیش‌نویس برگشت.');
    }

    /** این صفحه را صفحه‌ی اصلی سایت می‌کند (یا برمی‌دارد؛ آن‌وقت صفحه‌ی اصلی ثابت قبلی نمایش داده می‌شود). */
    public function setHome(Page $page)
    {
        abort_if($page->is_template, 404);
        if ($page->is_home) {
            $page->update(['is_home' => false]);

            return back()->with('success', 'صفحه‌ی اصلی سایت به حالت قبل (صفحه‌ی ثابت) برگشت.');
        }

        DB::transaction(function () use ($page) {
            Page::where('is_home', true)->update(['is_home' => false]);
            $page->update(['is_home' => true, 'is_active' => true]);
        });

        return back()->with('success', '«'.$page->title.'» صفحه‌ی اصلی سایت شد.');
    }

    public function preview(Request $request, Page $page)
    {
        return app(PublicPageController::class)->render($page, preview: true, embed: $request->boolean('embed'));
    }

    /** آدرس معتبر و یکتا؛ خطای اعتبارسنجی اگر کاربر آدرس رزروشده یا تکراری خواسته باشد. */
    private function validSlug(string $wanted, string $title, ?Page $ignore = null): string
    {
        $slug = PageBuilder::normalizeSlug($wanted);
        if ($slug === '') {
            return PageBuilder::suggestSlug($title, $ignore?->id);
        }
        if (PageBuilder::slugIsReserved($slug)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['slug' => "آدرس «{$slug}» را خود سایت استفاده می‌کند؛ آدرس دیگری انتخاب کنید."]);
        }
        if (Page::where('slug', $slug)->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id))->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['slug' => "صفحه‌ی دیگری با آدرس «{$slug}» وجود دارد."]);
        }

        return $slug;
    }
}
