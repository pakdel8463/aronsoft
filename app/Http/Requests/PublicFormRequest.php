<?php

namespace App\Http\Requests;

use App\Helpers\Fa;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base class for public (anonymous) forms:
 *  - normalises Persian digits / mobile numbers,
 *  - exposes a honeypot check (hidden input named "website").
 */
abstract class PublicFormRequest extends FormRequest
{
    /** Iranian mobile after normalisation */
    public const MOBILE_REGEX = '/^09\d{9}$/';

    public function authorize(): bool
    {
        return true;
    }

    /** Bots fill every input; humans never see this one. */
    public function isSpam(): bool
    {
        return $this->filled('website');
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['mobile', 'client_phone'] as $key) {
            if ($this->has($key)) {
                $merge[$key] = Fa::mobile((string) $this->input($key));
            }
        }
        foreach (['name', 'client_name', 'email', 'client_email', 'subject', 'company'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $merge[$key] = trim($this->input($key)) ?: null;
            }
        }
        if ($this->has('email') && is_string($this->input('email'))) {
            $merge['email'] = mb_strtolower(trim($this->input('email'))) ?: null;
        }

        $this->merge($merge);
    }

    public function messages(): array
    {
        return [
            'mobile.regex' => 'شماره موبایل معتبر نیست (مثال: ۰۹۱۲۱۲۳۴۵۶۷).',
            'client_phone.regex' => 'شماره موبایل معتبر نیست (مثال: ۰۹۱۲۱۲۳۴۵۶۷).',
        ];
    }
}
