<?php

namespace App\Http\Requests\Admin;

use App\Support\ProductPageSchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // دسترسی با middleware can:product-pages.manage
    }

    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug'));
        $this->merge([
            'slug' => $slug === '' ? null : mb_strtolower($slug),
            'accent' => strtoupper(trim((string) $this->input('accent'))) ?: null,
            'accent_2' => strtoupper(trim((string) $this->input('accent_2'))) ?: null,
        ]);
    }

    /** قالب این درخواست: ویرایش = قالب ذخیره‌شده، ایجاد = قالب انتخاب‌شده در فرم */
    public function templateKey(): string
    {
        $page = $this->route('product_page');
        if ($page instanceof \App\Models\ProductPage && $page->exists) {
            return $page->templateKey();
        }

        return ProductPageSchema::templateKey((string) $this->input('template'));
    }

    public function rules(): array
    {
        $t = $this->templateKey();
        $id = $this->route('product_page')?->id ?? $this->route('product_page');
        $hex = ['nullable', 'regex:/^#[0-9A-F]{6}$/'];

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9\x{0600}-\x{06FF}]+(?:-[a-z0-9\x{0600}-\x{06FF}]+)*$/u', Rule::unique('product_pages', 'slug')->ignore($id)],
            'tagline' => ['nullable', 'string', 'max:220'],
            'category_label' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(array_keys(\App\Models\ProductPage::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'accent' => $hex,
            'accent_2' => $hex,
            'logo' => ['nullable', 'string', 'max:500', ProductPageSchema::imageRule()],
            'card_image' => ['nullable', 'string', 'max:500', ProductPageSchema::imageRule()],
            'og_image' => ['nullable', 'string', 'max:500', ProductPageSchema::imageRule()],
            'icon' => ['nullable', 'string', Rule::in(ProductPageSchema::icons())],
            'links' => ['nullable', 'array'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'seo_keywords' => ['nullable', 'string', 'max:300'],
            'canonical_url' => ['nullable', 'string', 'max:500', ProductPageSchema::urlRule()],
            'noindex' => ['nullable', 'boolean'],
            'layout' => ['nullable', 'array'],
            'layout.*' => ['string', Rule::in(array_keys(ProductPageSchema::sections($t)))],
            'enabled' => ['nullable', 'array'],
            'sections' => ['nullable', 'array'],
        ];
        foreach (array_keys(ProductPageSchema::linkTypes()) as $k) {
            $rules["links.$k"] = ['nullable', 'string', 'max:500', ProductPageSchema::urlRule()];
        }

        $rules['template'] = ['nullable', 'string', Rule::in(array_keys(ProductPageSchema::templates()))];

        return $rules + ProductPageSchema::rules('sections', $t);
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام محصول', 'name_en' => 'نام انگلیسی', 'slug' => 'نشانی (slug)', 'tagline' => 'شعار',
            'category_label' => 'دسته‌ی نمایشی', 'accent' => 'رنگ اصلی', 'accent_2' => 'رنگ دوم',
            'seo_title' => 'عنوان سئو', 'seo_description' => 'توضیح سئو', 'canonical_url' => 'Canonical',
        ] + ProductPageSchema::attributes('sections', $this->templateKey());
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'نشانی فقط می‌تواند شامل حروف کوچک انگلیسی، عدد و خط‌تیره باشد.',
            'slug.unique' => 'این نشانی قبلاً استفاده شده است.',
            'accent.regex' => 'رنگ باید به شکل #RRGGBB باشد.',
            'accent_2.regex' => 'رنگ باید به شکل #RRGGBB باشد.',
        ];
    }

    /** داده‌ی آماده‌ی ذخیره در مدل (پاک‌سازی‌شده بر اساس اسکیما) */
    public function payload(): array
    {
        $v = $this->validated();

        $layoutRows = [];
        foreach ((array) ($v['layout'] ?? []) as $key) {
            $layoutRows[] = ['key' => $key, 'enabled' => ! empty(((array) $this->input('enabled'))[$key])];
        }

        $links = [];
        foreach (array_keys(ProductPageSchema::linkTypes()) as $k) {
            $u = trim((string) ($v['links'][$k] ?? ''));
            if ($u !== '') {
                $links[$k] = $u;
            }
        }

        $data = collect($v)->only([
            'name', 'name_en', 'slug', 'tagline', 'category_label', 'status', 'accent', 'accent_2',
            'logo', 'card_image', 'og_image', 'icon', 'seo_title', 'seo_description', 'seo_keywords', 'canonical_url',
        ])->map(fn ($x) => is_string($x) ? trim($x) : $x)->all();

        $data['sort_order'] = (int) ($v['sort_order'] ?? 0);
        $data['noindex'] = $this->boolean('noindex');
        $data['links'] = $links;
        $t = $this->templateKey();
        $data['template'] = $t;
        $data['layout'] = ProductPageSchema::normalizeLayout($layoutRows ?: null, $t);
        $data['sections'] = ProductPageSchema::sanitize($this->input('sections'), $t);
        // رنگ اول/دوم قالب به ستون‌های accent/accent_2 هم می‌رود (کارت فهرست و نوار چسبان)
        $pal = ProductPageSchema::paletteValues($t, $data['sections']['_palette'] ?? []);
        $vals = array_values($pal);
        $data['accent'] = $vals[0] ?? '#2F4FF5';
        $data['accent_2'] = $vals[1] ?? null;
        $data['published_at'] = ! empty($v['published_at']) ? \Illuminate\Support\Carbon::parse($v['published_at']) : null;
        if (($data['status'] ?? '') === 'published' && ! $data['published_at']) {
            $data['published_at'] = now();
        }
        foreach (['name_en', 'tagline', 'category_label', 'logo', 'card_image', 'og_image', 'icon', 'seo_title', 'seo_description', 'seo_keywords', 'canonical_url', 'accent_2'] as $k) {
            if (($data[$k] ?? null) === '') {
                $data[$k] = null;
            }
        }

        return $data;
    }
}
