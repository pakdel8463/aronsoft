<?php

namespace App\Http\Requests;

class ProformaRequest extends PublicFormRequest
{
    public function rules(): array
    {
        return [
            // price is intentionally NOT validated/used: totals come from config/aron_services.php
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id' => ['nullable', 'string', 'max:60'],
            'items.*.title' => ['nullable', 'string', 'max:200'],
            'client_name' => ['nullable', 'string', 'max:120'],
            'client_email' => ['required_without:client_phone', 'nullable', 'email', 'max:190'],
            'client_phone' => ['required_without:client_email', 'nullable', 'string', 'regex:'.self::MOBILE_REGEX],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'items.required' => 'حداقل یک خدمت را انتخاب کنید.',
            'client_email.required_without' => 'برای صدور پیش‌فاکتور، ایمیل یا شماره موبایل را وارد کنید.',
            'client_phone.required_without' => 'برای صدور پیش‌فاکتور، ایمیل یا شماره موبایل را وارد کنید.',
        ];
    }
}
