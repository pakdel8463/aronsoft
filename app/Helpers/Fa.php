<?php

namespace App\Helpers;

class Fa
{
    /** Persian/Arabic-Indic digits → latin digits. */
    public static function digits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /** Latin digits → Persian digits (for display). */
    public static function toFa(null|int|string $value): string
    {
        return strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    /**
     * Normalise an Iranian mobile number to 09xxxxxxxxx.
     * Returns the cleaned input unchanged when it cannot be normalised
     * (validation will then reject it).
     */
    public static function mobile(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $v = preg_replace('/[\s\-().]/u', '', (string) self::digits($value));

        if (preg_match('/^(?:\+98|0098|98)(9\d{9})$/', $v, $m)) {
            return '0'.$m[1];
        }
        if (preg_match('/^(9\d{9})$/', $v, $m)) {
            return '0'.$m[1];
        }

        return $v;
    }

    /** Persian-friendly price format: 12,000,000 */
    public static function money(int|float|string $amount): string
    {
        return self::toFa(number_format((float) $amount));
    }
}
