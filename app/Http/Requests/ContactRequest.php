<?php

namespace App\Http\Requests;

use AronLabs\Captcha\Rules\CaptchaRule;
use Illuminate\Validation\Rule;

class ContactRequest extends PublicFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'mobile' => ['required', 'string', 'regex:'.self::MOBILE_REGEX],
            'email' => ['nullable', 'email', 'max:190'],
            'subject' => ['nullable', 'string', Rule::in(config('aron_services.contact_subjects'))],
            'budget' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'captcha' => ['required', new CaptchaRule],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'message.min' => 'پیام شما کوتاه است؛ لطفاً حداقل ۱۰ نویسه بنویسید.',
            'captcha.required' => 'کد امنیتی را وارد کنید.',
        ];
    }
}
