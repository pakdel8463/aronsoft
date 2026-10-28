<?php

namespace App\Http\Requests;

use App\Captcha\Captcha;

class NewsletterRequest extends PublicFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:190'],
            'captcha' => Captcha::rules(),
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'captcha.required' => Captcha::REQUIRED_MESSAGE,
        ];
    }
}
