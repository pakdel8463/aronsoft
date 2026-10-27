<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * اسلاگ سازگار با فارسی: حروف فارسی حفظ می‌شوند (به‌جای حذف یا لاتین‌سازی)،
 * ارقام فارسی/عربی به لاتین تبدیل می‌شوند و نیم‌فاصله به خط تیره.
 */
class Slugger
{
    public static function make(?string $text, int $max = 120, string $fallback = 'item'): string
    {
        $t = trim((string) $text);
        if ($t === '') {
            return $fallback;
        }

        $t = strtr($t, [
            'ي' => 'ی', 'ى' => 'ی', 'ك' => 'ک', 'ۀ' => 'ه', 'ة' => 'ه', 'ؤ' => 'و', 'أ' => 'ا', 'إ' => 'ا',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
        // نیم‌فاصله و نشانه‌های جهت → جداکننده؛ اعراب و کشیده حذف
        $t = preg_replace('/[\x{200c}\x{200d}\x{200e}\x{200f}\x{202a}-\x{202e}]/u', '-', $t) ?? $t;
        $t = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $t) ?? $t;
        $t = mb_strtolower($t, 'UTF-8');
        $t = preg_replace('/[^\p{L}\p{N}]+/u', '-', $t) ?? $t;
        $t = trim($t, '-');
        if (mb_strlen($t) > $max) {
            $t = trim(mb_substr($t, 0, $max), '-');
        }

        return $t !== '' ? $t : $fallback;
    }

    /**
     * اسلاگ یکتا برای یک مدل (رکوردهای soft-deleted هم در نظر گرفته می‌شوند چون ایندکس یکتا شامل آن‌هاست).
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function unique(string $modelClass, ?string $value, ?int $ignoreId = null, string $column = 'slug', string $fallback = 'item'): string
    {
        $base = self::make($value, 110, $fallback);
        $slug = $base;
        $i = 2;

        while (self::exists($modelClass, $slug, $ignoreId, $column)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public static function exists(string $modelClass, string $slug, ?int $ignoreId = null, string $column = 'slug'): bool
    {
        /** @var Model $m */
        $m = new $modelClass;
        $q = in_array(SoftDeletes::class, class_uses_recursive($m), true) ? $modelClass::withTrashed() : $m->newQuery();

        return $q->where($column, $slug)
            ->when($ignoreId, fn ($qq) => $qq->where($m->getKeyName(), '!=', $ignoreId))
            ->exists();
    }
}
