<?php

namespace App\Http\Requests\Admin;

use App\Models\Portfolio;
use App\Support\JalaliInput;
use Illuminate\Foundation\Http\FormRequest;

class PortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $id = $this->route('portfolio')?->id;
        $date = function (string $attr, mixed $value, \Closure $fail) {
            if (filled($value) && ! JalaliInput::parse($value)) {
                $fail('قالب تاریخ معتبر نیست (نمونه: 1404/05/20).');
            }
        };

        return [
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255', 'unique:portfolios,slug'.($id ? ','.$id : '')],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description'       => ['nullable', 'string'],
            'client_name'       => ['nullable', 'string', 'max:255'],
            'preview_link'      => ['nullable', 'string', 'max:255', 'regex:/^(?!\s*(javascript|data|vbscript):)/i'],
            'featured_image'    => ['nullable', 'string', 'max:500', 'regex:/^(?!\s*(javascript|data|vbscript):)/i'],
            'technologies'      => ['nullable', 'array', 'max:30'],
            'technologies.*'    => ['string', 'max:60'],
            'tags'              => ['nullable', 'array', 'max:30'],
            'tags.*'            => ['string', 'max:60'],
            'categories'        => ['nullable', 'array'],
            'categories.*'      => ['integer', 'exists:categories,id'],
            'team_members'      => ['nullable', 'array'],
            'team_members.*'    => ['integer', 'exists:team_members,id'],
            'gallery'           => ['nullable', 'array', 'max:60'],
            'gallery.*'         => ['integer'],
            'seo_title'         => ['nullable', 'string', 'max:255'],
            'seo_description'   => ['nullable', 'string', 'max:500'],
            'start_date'        => ['nullable', 'string', $date],
            'end_date'          => ['nullable', 'string', $date],
            'progress'          => ['nullable', 'integer', 'min:0', 'max:100'],
            'status'            => ['required', 'in:'.implode(',', array_keys(Portfolio::STATUSES))],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان', 'slug' => 'نامک', 'client_name' => 'نام کارفرما', 'preview_link' => 'لینک پیش‌نمایش',
            'seo_title' => 'عنوان سئو', 'seo_description' => 'توضیحات سئو', 'start_date' => 'تاریخ شروع',
            'end_date' => 'تاریخ پایان', 'progress' => 'درصد پیشرفت', 'status' => 'وضعیت',
            'short_description' => 'توضیح کوتاه', 'featured_image' => 'تصویر شاخص',
        ];
    }

    /** مقادیر آماده‌ی ذخیره برای ستون‌های جدول portfolios. */
    public function columns(?Portfolio $current = null): array
    {
        $d = $this->validated();
        $slug = Portfolio::uniqueSlug(($d['slug'] ?? null) ?: $d['title'], $current?->id);
        $description = filled($d['description'] ?? null) ? \App\Support\HtmlSanitizer::clean($d['description']) : null;

        return [
            'title'             => $d['title'],
            'slug'              => $slug,
            'short_description' => $d['short_description'] ?? null,
            'description'       => $description,
            'client_name'       => $d['client_name'] ?? null,
            'preview_link'      => $d['preview_link'] ?? null,
            'featured_image'    => $d['featured_image'] ?? null,
            'technologies'      => $d['technologies'] ?? [],
            'seo_title'         => $d['seo_title'] ?? null,
            'seo_description'   => $d['seo_description'] ?? null,
            'seo_score'         => Portfolio::computeSeoScore($d),
            'start_date'        => JalaliInput::parse($d['start_date'] ?? null),
            'end_date'          => JalaliInput::parse($d['end_date'] ?? null),
            'progress'          => (int) ($d['progress'] ?? 0),
            'status'            => $d['status'],
        ];
    }
}
