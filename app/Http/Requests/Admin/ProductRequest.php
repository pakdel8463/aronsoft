<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        // ارقام فارسی و جداکننده‌ی هزارگان در قیمت
        foreach (['price', 'discount_price'] as $f) {
            if ($this->has($f)) {
                $v = strtr((string) $this->input($f), ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']);
                $this->merge([$f => preg_replace('/[,٬\s]/u', '', $v)]);
            }
        }
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;
        $safeUrl = 'regex:/^(?!\s*(javascript|data|vbscript):)/i';
        $managedOrUrl = 'regex:#^(storage/'.Product::IMAGE_DIR.'/[A-Za-z0-9._-]+|https?://\S+|images/\S+)$#';

        return [
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255', 'unique:products,slug'.($id ? ','.$id : '')],
            'type'              => ['required', 'in:'.implode(',', array_keys(Product::TYPES))],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['required', 'string'],
            'price'             => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'discount_price'    => ['nullable', 'integer', 'min:0', 'lt:price'],
            'version'           => ['nullable', 'string', 'max:30'],
            'demo_link'         => ['nullable', 'url', 'max:255', $safeUrl],
            'download_url'      => ['nullable', 'url', 'max:500', $safeUrl],
            'download_file'     => ['nullable', 'file', 'max:102400', 'extensions:zip,gz,tgz,tar,rar,7z,sh,pdf'],
            'remove_file'       => ['nullable', 'boolean'],
            'featured_image'    => ['nullable', 'string', 'max:500', $managedOrUrl],
            'gallery'           => ['nullable', 'array', 'max:12'],
            'gallery.*'         => ['string', 'max:500', $managedOrUrl],
            'is_for_sale'       => ['nullable', 'boolean'],
            'is_active'         => ['nullable', 'boolean'],
            'seo_title'         => ['nullable', 'string', 'max:255'],
            'seo_description'   => ['nullable', 'string', 'max:500'],
            'seo_keywords'      => ['nullable', 'string', 'max:255'],
            'canonical_url'     => ['nullable', 'url', 'max:255', $safeUrl],
        ];
    }

    public function messages(): array
    {
        return [
            'discount_price.lt'          => 'قیمت با تخفیف باید کمتر از قیمت اصلی باشد.',
            'download_file.extensions'   => 'فرمت فایل دانلود مجاز نیست (zip، gz، tar، rar، 7z، sh، pdf).',
            'download_file.max'          => 'حجم فایل دانلود حداکثر ۱۰۰ مگابایت است.',
            'featured_image.regex'       => 'آدرس تصویر معتبر نیست.',
            'gallery.*.regex'            => 'آدرس تصویر گالری معتبر نیست.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان', 'slug' => 'نامک', 'type' => 'نوع محصول', 'description' => 'توضیحات',
            'price' => 'قیمت', 'discount_price' => 'قیمت با تخفیف', 'version' => 'نسخه', 'demo_link' => 'لینک دمو',
            'download_url' => 'لینک دانلود', 'download_file' => 'فایل دانلود', 'seo_title' => 'عنوان سئو',
            'seo_description' => 'توضیحات سئو', 'canonical_url' => 'آدرس Canonical', 'short_description' => 'توضیح کوتاه',
        ];
    }
}
