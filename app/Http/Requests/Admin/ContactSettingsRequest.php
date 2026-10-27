<?php

namespace App\Http\Requests\Admin;

use App\Helpers\Fa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ContactSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('settings.contact');
    }

    protected function prepareForValidation(): void
    {
        $in = $this->all();

        foreach (['telegram', 'instagram'] as $k) {
            if (isset($in[$k]) && is_string($in[$k])) {
                $in[$k] = ltrim(trim($in[$k]), '@');
            }
        }
        foreach (['phone', 'mobile', 'whatsapp', 'postal_code'] as $k) {
            if (isset($in[$k]) && is_string($in[$k])) {
                $in[$k] = Fa::digits(trim($in[$k]));
            }
        }
        if (isset($in['map']) && is_array($in['map'])) {
            foreach (['lat', 'lng', 'zoom'] as $k) {
                if (isset($in['map'][$k]) && is_string($in['map'][$k])) {
                    $in['map'][$k] = str_replace('٫', '.', Fa::digits(trim($in['map'][$k])));
                }
            }
        }

        $this->replace($in);
    }

    public function rules(): array
    {
        $phone = ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\-\s]*$/'];
        $user = ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_.]+$/'];

        return [
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\-\s]*$/'],
            'phone_display' => ['required', 'string', 'max:40'],
            'mobile' => $phone,
            'whatsapp' => $phone,
            'email' => ['required', 'email', 'max:190'],
            'telegram' => $user,
            'instagram' => $user,
            'linkedin' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'url', 'max:255'],
            'x' => ['nullable', 'url', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'address_street' => ['nullable', 'string', 'max:300'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\-\s]+$/'],
            'hours' => ['nullable', 'string', 'max:200'],
            'hours_note' => ['nullable', 'string', 'max:300'],
            'map.lat' => ['required', 'numeric', 'between:-90,90'],
            'map.lng' => ['required', 'numeric', 'between:-180,180'],
            'map.zoom' => ['required', 'integer', 'between:1,19'],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'شماره تلفن',
            'phone_display' => 'نمایش شماره تلفن',
            'mobile' => 'موبایل',
            'whatsapp' => 'واتس‌اپ',
            'email' => 'ایمیل',
            'telegram' => 'تلگرام',
            'instagram' => 'اینستاگرام',
            'linkedin' => 'لینکدین',
            'github' => 'گیت‌هاب',
            'x' => 'ایکس (توییتر)',
            'address' => 'آدرس',
            'address_street' => 'آدرس خیابانی',
            'city' => 'شهر',
            'postal_code' => 'کدپستی',
            'hours' => 'ساعات کاری',
            'hours_note' => 'توضیح ساعات کاری',
            'map.lat' => 'عرض جغرافیایی',
            'map.lng' => 'طول جغرافیایی',
            'map.zoom' => 'بزرگنمایی نقشه',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute را وارد کنید.',
            'email' => 'ایمیل واردشده معتبر نیست.',
            'url' => ':attribute باید یک آدرس معتبر (با http یا https) باشد.',
            'max' => ':attribute بیش از حد طولانی است.',
            'numeric' => ':attribute باید عدد باشد.',
            'integer' => ':attribute باید عدد صحیح باشد.',
            'phone.regex' => 'شماره تلفن فقط می‌تواند شامل ارقام و خط تیره باشد.',
            'mobile.regex' => 'شماره موبایل فقط می‌تواند شامل ارقام و خط تیره باشد.',
            'whatsapp.regex' => 'شماره واتس‌اپ فقط می‌تواند شامل ارقام و خط تیره باشد.',
            'postal_code.regex' => 'کدپستی فقط می‌تواند شامل ارقام باشد.',
            'telegram.regex' => 'نام کاربری تلگرام فقط می‌تواند شامل حروف انگلیسی، عدد، نقطه و _ باشد.',
            'instagram.regex' => 'نام کاربری اینستاگرام فقط می‌تواند شامل حروف انگلیسی، عدد، نقطه و _ باشد.',
            'map.lat.between' => 'عرض جغرافیایی باید بین ۹۰- و ۹۰ باشد.',
            'map.lng.between' => 'طول جغرافیایی باید بین ۱۸۰- و ۱۸۰ باشد.',
            'map.zoom.between' => 'بزرگنمایی نقشه باید بین ۱ و ۱۹ باشد.',
        ];
    }
}
