<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductPageRequest;
use App\Models\ProductPage;
use App\Support\ProductPageSchema;
use Illuminate\Http\Request;

class ProductPageController extends Controller
{
    /** ساخت صفحه‌های پیش‌فرض (فقط موارد ناموجود؛ پیش‌نویس) */
    public function seedDefaults()
    {
        $before = ProductPage::count();
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ProductPagesSeeder', '--force' => true]);
        $made = ProductPage::count() - $before;

        return redirect()->route('panel.product-pages.index')
            ->with('success', $made > 0 ? "{$made} صفحه‌ی محصول به‌صورت پیش‌نویس ساخته شد." : 'صفحه‌های پیش‌فرض از قبل وجود دارند.');
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $status = $request->query('status');

        $pages = ProductPage::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('name_en', 'like', $like)->orWhere('slug', 'like', $like));
            })
            ->when(in_array($status, array_keys(ProductPage::STATUSES), true), fn ($query) => $query->where('status', $status))
            ->orderBy('sort_order')->orderBy('id')
            ->paginate(20)->withQueryString();

        return view('admin.dash.product-pages.index', [
            'pages' => $pages,
            'q' => $q,
            'status' => $status,
            'counts' => ProductPage::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function create(Request $request)
    {
        $tpl = (string) $request->query('template');
        if (! isset(ProductPageSchema::templates()[$tpl])) {
            return view('admin.dash.product-pages.choose-template', ['templates' => ProductPageSchema::templates()]);
        }

        $pal = array_values(ProductPageSchema::paletteValues($tpl, []));
        $page = new ProductPage([
            'template' => $tpl, 'status' => 'draft', 'accent' => $pal[0] ?? '#2F4FF5', 'accent_2' => $pal[1] ?? null,
            'sort_order' => (int) ProductPage::max('sort_order') + 10,
            'links' => [], 'layout' => ProductPageSchema::defaultLayout($tpl), 'sections' => ProductPageSchema::sample($tpl),
        ]);

        return view('admin.dash.product-pages.form', ['page' => $page]);
    }

    /** جایگزینی محتوای صفحه با نمونه‌ی قالبش */
    public function loadSample(ProductPage $product_page)
    {
        $t = $product_page->templateKey();
        $product_page->update([
            'sections' => ProductPageSchema::sample($t),
            'layout' => ProductPageSchema::defaultLayout($t),
        ]);

        return redirect()->route('panel.product-pages.edit', $product_page)->with('success', 'محتوای نمونه‌ی قالب بارگذاری شد.');
    }

    public function store(ProductPageRequest $request)
    {
        $data = $request->payload();
        $data['created_by'] = auth()->id();
        $data['slug'] = $data['slug'] ?: ProductPage::uniqueSlug($data['name_en'] ?? $data['name']);

        $page = ProductPage::create($data);

        return redirect()->route('panel.product-pages.edit', $page)->with('success', 'صفحه‌ی محصول ساخته شد.');
    }

    public function show(ProductPage $product_page)
    {
        return redirect()->route('panel.product-pages.edit', $product_page);
    }

    public function edit(ProductPage $product_page)
    {
        return view('admin.dash.product-pages.form', ['page' => $product_page]);
    }

    public function update(ProductPageRequest $request, ProductPage $product_page)
    {
        $data = $request->payload();
        $data['slug'] = $data['slug'] ?: ProductPage::uniqueSlug($data['name_en'] ?? $data['name'], $product_page->id);
        $product_page->update($data);

        return redirect()->route('panel.product-pages.edit', $product_page)->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(ProductPage $product_page)
    {
        $product_page->delete();

        return redirect()->route('panel.product-pages.index')->with('success', 'صفحه‌ی محصول حذف شد.');
    }

    /** انتشار / بازگشت به پیش‌نویس */
    public function toggle(ProductPage $product_page)
    {
        $publish = $product_page->status !== 'published';
        $product_page->update([
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? ($product_page->published_at ?? now()) : $product_page->published_at,
        ]);

        return back()->with('success', $publish ? 'صفحه منتشر شد.' : 'صفحه به پیش‌نویس برگشت.');
    }

    public function duplicate(ProductPage $product_page)
    {
        $copy = $product_page->replicate(['slug', 'published_at']);
        $copy->name = $product_page->name.' (کپی)';
        $copy->slug = ProductPage::uniqueSlug($product_page->slug.'-copy');
        $copy->status = 'draft';
        $copy->sort_order = (int) ProductPage::max('sort_order') + 10;
        $copy->created_by = auth()->id();
        $copy->save();

        return redirect()->route('panel.product-pages.edit', $copy)->with('success', 'نسخه‌ی کپی ساخته شد (پیش‌نویس).');
    }
}
