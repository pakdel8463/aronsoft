<?php

namespace App\Captcha;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** قانون اعتبارسنجی فیلد captcha (ویجت <x-captcha />). */
class CaptchaRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Captcha::enabled()) {
            return;
        }
        if (! Captcha::verify(is_string($value) ? $value : null)) {
            $fail('بررسی امنیتی انجام نشد؛ چند ثانیه صبر کنید و دوباره ارسال کنید.');
        }
    }
}
