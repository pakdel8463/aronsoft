<?php

namespace App\Support;

use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

/** تبدیل تاریخ ورودی پنل (شمسی Y/m/d، با ارقام فارسی/عربی یا میلادی) به Carbon و برعکس. */
class JalaliInput
{
    public static function normalize(?string $v): string
    {
        $v = trim((string) $v);
        $v = strtr($v, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);

        return str_replace(['-', '.', '\\'], '/', $v);
    }

    public static function parse(?string $v): ?Carbon
    {
        $v = self::normalize($v);
        if ($v === '') {
            return null;
        }
        if (! preg_match('#^(\d{4})/(\d{1,2})/(\d{1,2})$#', $v, $m)) {
            return null;
        }
        [$y, $mo, $d] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        try {
            if ($y >= 1700) {
                return checkdate($mo, $d, $y) ? Carbon::create($y, $mo, $d)->startOfDay() : null;
            }
            if ($mo < 1 || $mo > 12 || $d < 1 || $d > 31) {
                return null;
            }

            return Jalalian::fromFormat('Y/m/d', sprintf('%04d/%02d/%02d', $y, $mo, $d))->toCarbon()->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function format($date): string
    {
        if (! $date) {
            return '';
        }

        return Jalalian::fromCarbon(Carbon::parse($date))->format('Y/m/d');
    }
}
