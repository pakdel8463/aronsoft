<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $type = $request->query('type');
        $state = $request->query('state');

        $products = Product::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like));
            })
            ->when(array_key_exists((string) $type, Product::TYPES), fn ($query) => $query->where('type', $type))
            ->when($state === 'active', fn ($query) => $query->where('is_active', true))
            ->when($state === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.dash.products.index', compact('products', 'q', 'type', 'state'));
    }

    public function create()
    {
        return view('admin.dash.products.create', [
            'product' => new Product(['type' => 'wordpress_theme', 'version' => '1.0.0', 'price' => 0, 'is_active' => true, 'is_for_sale' => false]),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $this->payload($request);
        $data['slug'] = Product::uniqueSlug($request->input('slug') ?: $request->input('title'));
        if ($request->hasFile('download_file')) {
            $f = $request->file('download_file');
            $data['file_path'] = $f->storeAs(Product::FILE_DIR, Str::random(20).'.'.$f->getClientOriginalExtension(), 'local');
        }

        $product = Product::create($data);

        return redirect()->route('panel.products.edit', $product)->with('success', 'محصول با موفقیت ایجاد شد.');
    }

    public function show(Product $product)
    {
        return redirect()->route('panel.products.edit', $product);
    }

    public function edit(Product $product)
    {
        return view('admin.dash.products.edit', compact('product'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $this->payload($request);
        $data['slug'] = Product::uniqueSlug($request->input('slug') ?: $product->slug ?: $request->input('title'), $product->id);

        $oldImage = $product->featured_image;
        $oldGallery = (array) $product->gallery;
        $oldFile = $product->file_path;

        if ($request->hasFile('download_file')) {
            $f = $request->file('download_file');
            $data['file_path'] = $f->storeAs(Product::FILE_DIR, Str::random(20).'.'.$f->getClientOriginalExtension(), 'local');
        } elseif ($request->boolean('remove_file')) {
            $data['file_path'] = null;
        }

        $product->update($data);

        if ($oldImage && $oldImage !== $product->featured_image && ! in_array($oldImage, (array) $product->gallery, true)) {
            Product::deleteManagedImage($oldImage);
        }
        foreach (array_diff($oldGallery, (array) $product->gallery) as $gone) {
            if ($gone !== $product->featured_image) {
                Product::deleteManagedImage($gone);
            }
        }
        if ($oldFile && $oldFile !== $product->file_path) {
            Product::deleteManagedFile($oldFile);
        }

        return redirect()->route('panel.products.index')->with('success', 'محصول بروزرسانی شد.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        Product::deleteManagedImage($product->featured_image);
        foreach ((array) $product->gallery as $g) {
            Product::deleteManagedImage($g);
        }
        Product::deleteManagedFile($product->file_path);

        return redirect()->route('panel.products.index')->with('success', 'محصول حذف شد.');
    }

    public function toggleStatus(Request $request, Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'is_active' => $product->is_active]);
        }

        return back()->with('success', $product->is_active ? 'محصول فعال شد.' : 'محصول غیرفعال شد.');
    }

    /** آپلود تصویر (تصویر شاخص / گالری) — آژاکس؛ مسیر نسبی قابل ذخیره برمی‌گرداند. */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:12'],
            'images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ], [
            'images.required' => 'تصویری ارسال نشده است.',
            'images.*.image'  => 'فایل انتخاب‌شده تصویر معتبر نیست.',
            'images.*.mimes'  => 'فقط فرمت‌های jpg، png و webp مجاز است.',
            'images.*.max'    => 'حجم هر تصویر حداکثر ۴ مگابایت است.',
        ]);

        $files = [];
        foreach ($request->file('images') as $file) {
            $name = Str::random(24).'.'.($file->extension() ?: 'jpg');
            $file->storeAs(Product::IMAGE_DIR, $name, 'public');
            $path = 'storage/'.Product::IMAGE_DIR.'/'.$name;
            $files[] = ['id' => null, 'file' => $name, 'path' => $path, 'url' => asset($path)];
        }

        return response()->json(['status' => 'success', 'files' => $files]);
    }

    private function payload(ProductRequest $request): array
    {
        $d = $request->validated();
        $gallery = array_values(array_unique(array_filter((array) ($d['gallery'] ?? []))));

        return [
            'title'             => $d['title'],
            'type'              => $d['type'],
            'short_description' => $d['short_description'] ?? null,
            'description'       => \App\Support\HtmlSanitizer::clean($d['description']),
            'price'             => (int) ($d['price'] ?? 0),
            'discount_price'    => ! empty($d['discount_price']) ? (int) $d['discount_price'] : null,
            'version'           => $d['version'] ?? '1.0.0' ?: '1.0.0',
            'demo_link'         => $d['demo_link'] ?? null,
            'download_url'      => $d['download_url'] ?? null,
            'featured_image'    => $d['featured_image'] ?? null,
            'gallery'           => $gallery ?: null,
            'is_for_sale'       => $request->boolean('is_for_sale'),
            'is_active'         => $request->boolean('is_active'),
            'seo_title'         => $d['seo_title'] ?? null,
            'seo_description'   => $d['seo_description'] ?? null,
            'seo_keywords'      => $d['seo_keywords'] ?? null,
            'canonical_url'     => $d['canonical_url'] ?? null,
        ];
    }
}
