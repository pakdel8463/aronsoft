@php
    $isEdit = $product->exists;
    $action = $isEdit ? route('panel.products.update', $product) : route('panel.products.store');
    $toUrl = fn ($p) => preg_match('#^(https?:)?//#', (string) $p) ? $p : asset(ltrim((string) $p, '/'));
    $galleryItems = collect((array) $product->gallery)->map(fn ($p) => ['value' => $p, 'url' => $toUrl($p)])->all();
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid items-start gap-5 lg:grid-cols-3">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="grid gap-5 lg:col-span-2">
        <x-admin.card title="اطلاعات محصول" icon="package">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="title" label="عنوان محصول" required :value="$product->title" class="" />
                <x-admin.select name="type" label="نوع محصول" required :options="\App\Models\Product::TYPES" :selected="$product->type" />
                <x-admin.input name="slug" label="نامک (Slug)" :value="$product->slug" ltr hint="خالی بگذارید تا از روی عنوان ساخته شود." />
                <x-admin.input name="version" label="نسخه" :value="$product->version" ltr placeholder="1.0.0" />
                <x-admin.textarea name="short_description" label="توضیح کوتاه" :value="$product->short_description" rows="2" hint="در کارت محصول نمایش داده می‌شود." class="sm:col-span-2" />
                <div class="sm:col-span-2">
                    @include('admin.dash.portfolio.partials.rich', ['name' => 'description', 'value' => $product->description, 'label' => 'توضیحات کامل', 'rows' => 14])
                    @error('description')@enderror
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="تصاویر" subtitle="تصویر شاخص و گالری (حداکثر ۴ مگابایت برای هر تصویر)" icon="images">
            <div class="grid gap-6">
                <x-admin.image-field name="featured_image" label="تصویر شاخص" :value="$product->featured_image" :upload-url="route('panel.products.upload-image')" />
                <x-admin.gallery-field name="gallery" label="گالری" :items="$galleryItems" :upload-url="route('panel.products.upload-image')" :max="12" />
            </div>
        </x-admin.card>

        <x-admin.card title="دانلود و دمو" icon="download-simple">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="demo_link" label="لینک دمو / گیت‌هاب" type="url" :value="$product->demo_link" placeholder="https://" class="" />
                <x-admin.input name="download_url" label="لینک دانلود خارجی" type="url" :value="$product->download_url" placeholder="https://" hint="اگر فایل آپلود نمی‌کنید." />
                <div class="sm:col-span-2">
                    <x-admin.field label="فایل دانلود" name="download_file" hint="zip، gz، tar، rar، 7z، sh یا pdf — حداکثر ۱۰۰ مگابایت. فایل در مسیر خصوصی سرور ذخیره می‌شود.">
                        <input type="file" name="download_file" class="a-input file:me-3 file:rounded-lg file:border-0 file:bg-aron-50 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-aron-700">
                    </x-admin.field>
                    @if ($isEdit && $product->file_path)
                        <div class="mt-3 flex flex-wrap items-center gap-3 rounded-xl bg-slate-50 px-4 py-2.5 text-sm">
                            <i class="ph ph-file-zip text-lg text-aron-600"></i>
                            <span class="font-semibold text-slate-600" dir="ltr">{{ basename($product->file_path) }}</span>
                            <x-admin.checkbox name="remove_file" label="حذف فایل فعلی" value="1" unchecked="0" />
                        </div>
                    @endif
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="سئو" icon="magnifying-glass">
            <div class="grid gap-4">
                <x-admin.input name="seo_title" label="عنوان سئو" :value="$product->seo_title" hint="خالی = عنوان محصول" />
                <x-admin.textarea name="seo_description" label="توضیحات سئو" :value="$product->seo_description" rows="3" hint="حداکثر ۵۰۰ نویسه؛ بهترین طول ۷۰ تا ۱۶۰" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="seo_keywords" label="کلمات کلیدی" :value="$product->seo_keywords" hint="با ویرگول جدا کنید" />
                    <x-admin.input name="canonical_url" label="آدرس Canonical" type="url" :value="$product->canonical_url" placeholder="https://" />
                </div>
            </div>
        </x-admin.card>
    </div>

    <div class="grid gap-5">
        <x-admin.card title="انتشار" icon="paper-plane-tilt">
            <div class="grid gap-4">
                <x-admin.toggle name="is_active" label="فعال (نمایش در سایت)" :checked="old('is_active', $product->is_active ?? true)" />
                <x-admin.toggle name="is_for_sale" label="قابل خرید" hint="خاموش = فقط معرفی محصول" :checked="old('is_for_sale', $product->is_for_sale)" />
                <div class="flex flex-wrap gap-2">
                    <x-admin.button type="submit" icon="floppy-disk" loading>{{ $isEdit ? 'بروزرسانی' : 'ذخیره' }}</x-admin.button>
                    <x-admin.button :href="route('panel.products.index')" variant="secondary">انصراف</x-admin.button>
                    @if ($isEdit && $product->is_active)
                        <x-admin.button :href="route('products.show', $product->slug)" variant="ghost" icon="arrow-square-out" target="_blank" rel="noopener">مشاهده</x-admin.button>
                    @endif
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="قیمت" icon="coins">
            <div class="grid gap-4">
                <x-admin.input name="price" label="قیمت (تومان)" :value="$product->price ?? 0" type="text" inputmode="numeric" ltr hint="۰ = رایگان" data-money />
                <x-admin.input name="discount_price" label="قیمت با تخفیف (تومان)" :value="$product->discount_price" type="text" inputmode="numeric" ltr hint="اختیاری؛ باید کمتر از قیمت اصلی باشد." data-money />
                <p class="rounded-xl bg-slate-50 px-3.5 py-2.5 text-xs leading-6 text-slate-500" data-price-preview></p>
            </div>
        </x-admin.card>
    </div>
</form>
<script>
(function () {
    const p = document.querySelector('[name=price]'), d = document.querySelector('[name=discount_price]'), out = document.querySelector('[data-price-preview]');
    if (!p) return;
    const num = el => parseInt(String(el.value).replace(/[^\d]/g, ''), 10) || 0;
    const fmt = n => n.toLocaleString('fa-IR');
    function up() {
        const a = num(p), b = num(d);
        if (!a) out.textContent = 'محصول رایگان است.';
        else if (b && b < a) out.textContent = 'قیمت نهایی: ' + fmt(b) + ' تومان (' + fmt(Math.round((a - b) * 100 / a)) + '٪ تخفیف)';
        else out.textContent = 'قیمت نهایی: ' + fmt(a) + ' تومان';
    }
    [p, d].forEach(el => el.addEventListener('input', up)); up();
})();
</script>
