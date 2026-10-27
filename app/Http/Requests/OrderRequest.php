<?php

namespace App\Http\Requests;

use AronLabs\Captcha\Rules\CaptchaRule;
use Illuminate\Validation\Rule;

class OrderRequest extends PublicFormRequest
{
    public function rules(): array
    {
        $type = (string) $this->input('type');
        $types = array_keys(config('aron_services.order.types'));
        $featureKeys = array_keys(config("aron_services.order.types.{$type}.features", []));

        return [
            'type' => ['required', 'string', Rule::in($types)],
            'budget' => ['nullable', 'string', Rule::in(array_keys(config('aron_services.order.budgets')))],
            'deadline' => ['nullable', 'string', Rule::in(array_keys(config('aron_services.order.deadlines')))],
            'features' => ['nullable', 'array', 'max:20'],
            'features.*' => ['string', Rule::in($featureKeys)],
            'description' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,jpg,jpeg,png,webp'],

            'name' => ['required', 'string', 'min:3', 'max:120'],
            'mobile' => ['required', 'string', 'regex:'.self::MOBILE_REGEX],
            'email' => ['nullable', 'email', 'max:190'],
            'contact_time' => ['nullable', 'string', Rule::in(array_keys(config('aron_services.order.contact_times')))],
            'consent' => ['accepted'],
            'captcha' => ['required', new CaptchaRule],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'type.required' => 'لطفاً نوع پروژه را انتخاب کنید.',
            'attachment.max' => 'حجم فایل پیوست نباید بیش از ۵ مگابایت باشد.',
            'attachment.mimes' => 'فرمت فایل پیوست مجاز نیست (PDF، Word، Excel، تصویر یا ZIP).',
            'consent.accepted' => 'برای ثبت درخواست، باید با تماس تیم آرون سافت موافقت کنید.',
            'captcha.required' => 'کد امنیتی را وارد کنید.',
        ];
    }
}
