<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * اطلاعات تماس شرکت: پیش‌فرض‌ها در config('aron_services.contact') و بازنویسی‌ها (از پنل) به‌صورت JSON
 * در جدول settings با کلید `contact_info`. هرگز استثنا پرتاب نمی‌کند (جدول/دیتابیس نبود → پیش‌فرض‌ها).
 */
class ContactInfo
{
    public const SETTING_KEY = 'contact_info';

    public const CACHE_KEY = 'aron.contact_info';

    /** فیلدهای متنی قابل ذخیره (به‌جز map). */
    public const FIELDS = [
        'phone', 'phone_display', 'mobile', 'whatsapp', 'email',
        'telegram', 'instagram', 'linkedin', 'github', 'x',
        'address', 'address_street', 'city', 'postal_code', 'hours', 'hours_note',
    ];

    public static function defaults(): array
    {
        return (array) (config('aron_services.contact') ?: config('aron_contact', []));
    }

    /** پیش‌فرض‌ها + بازنویسی‌های ذخیره‌شده. */
    public static function all(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                $raw = Setting::where('key', self::SETTING_KEY)->value('value');

                return self::merge(self::defaults(), $raw ? json_decode($raw, true) : null);
            });
        } catch (\Throwable $e) {
            return self::merge(self::defaults(), null);
        }
    }

    public static function get(string $key, $default = null)
    {
        return self::all()[$key] ?? $default;
    }

    /** ذخیره‌ی بازنویسی‌ها (کل مجموعه‌ی فیلدها) و پاک‌کردن کش. */
    public static function save(array $data): void
    {
        Setting::updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
        self::forget();
    }

    /** حذف بازنویسی‌ها → بازگشت به پیش‌فرض. */
    public static function reset(): void
    {
        Setting::where('key', self::SETTING_KEY)->delete();
        self::forget();
    }

    public static function forget(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable $e) {
            // کش در دسترس نیست
        }
    }

    /** E.164 تقریبی برای شماره‌ی ایرانی: +982122451912 (یا '' اگر شماره‌ای نیست). */
    public static function telUri(?string $number = null): string
    {
        $digits = preg_replace('/\D/', '', \App\Helpers\Fa::digits($number ?? (string) self::get('phone', ''))) ?? '';
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0098')) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '98') && strlen($digits) >= 11) {
            $digits = substr($digits, 2);
        }

        return '+98'.ltrim($digits, '0');
    }

    public static function telegramUrl(): string
    {
        $u = (string) self::get('telegram', '');

        return $u !== '' ? 'https://t.me/'.$u : '';
    }

    public static function instagramUrl(): string
    {
        $u = (string) self::get('instagram', '');

        return $u !== '' ? 'https://www.instagram.com/'.$u.'/' : '';
    }

    public static function whatsappUrl(): string
    {
        $n = ltrim(self::telUri((string) self::get('whatsapp', '')), '+');

        return $n !== '' ? 'https://wa.me/'.$n : '';
    }

    /** لینک‌های شبکه‌های اجتماعی پرشده (برای JSON-LD). */
    public static function sameAs(): array
    {
        return array_values(array_filter([
            self::instagramUrl(),
            self::telegramUrl(),
            (string) self::get('linkedin', ''),
            (string) self::get('github', ''),
            (string) self::get('x', ''),
        ]));
    }

    private static function merge(array $defaults, ?array $overrides): array
    {
        $out = $defaults;
        foreach ((array) $overrides as $k => $v) {
            if ($k === 'map') {
                $out['map'] = array_replace((array) ($defaults['map'] ?? []), array_filter((array) $v, fn ($x) => $x !== null && $x !== ''));
            } elseif (in_array($k, self::FIELDS, true)) {
                $out[$k] = (string) $v; // رشته‌ی خالی هم معتبر است: فیلد پنهان می‌شود
            }
        }

        return $out;
    }
}
