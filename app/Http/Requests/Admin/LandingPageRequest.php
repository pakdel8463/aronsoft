<?php

namespace App\Http\Requests\Admin;

use App\Models\LandingPage;
use Illuminate\Foundation\Http\FormRequest;

class LandingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $id = $this->route('landing')?->id;

        return [
            'title'           => ['required', 'string', 'max:255'],
            'slug'            => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\-_]+$/', 'unique:landing_pages,slug'.($id ? ','.$id : '')],
            'template'        => ['required', 'in:'.implode(',', array_keys(LandingPage::templates())).',cms'],
            'portfolio_id' => ['nullable','integer','exists:portfolios,id'],
            'appearance' => ['nullable','array'],
            'appearance.accent' => ['nullable','regex:/^#[0-9a-fA-F]{6}$/'],
            'appearance.background' => ['nullable','regex:/^#[0-9a-fA-F]{6}$/'],
            'appearance.foreground' => ['nullable','regex:/^#[0-9a-fA-F]{6}$/'],
            'sections_json' => ['nullable','json'],
            'seo_title'       => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex'         => 'نامک فقط می‌تواند شامل حروف لاتین، عدد، خط تیره و زیرخط باشد.',
            'seo_description.max' => 'توضیحات سئو حداکثر ۲۵۵ نویسه است.',
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'عنوان', 'slug' => 'نامک', 'template' => 'قالب', 'seo_title' => 'عنوان سئو', 'seo_description' => 'توضیحات سئو'];
    }
}
