<?php

namespace App\Http\Requests;

use AronLabs\Captcha\Rules\CaptchaRule;

class NewsletterRequest extends PublicFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:190'],
            'captcha' => ['required', new CaptchaRule],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'captcha.required' => 'کد امنیتی را وارد کنید.',
        ];
    }
}
