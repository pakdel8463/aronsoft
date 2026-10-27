<?php

namespace App\Support;

class TextStats
{
    public static function plain(?string $html): string
    {
        $t = preg_replace('#<(script|style)\b.*?</\1>#is', ' ', (string) $html) ?? '';
        $t = preg_replace('#<(br|/p|/div|/li|/h[1-6]|/tr)\b[^>]*>#i', ' ', $t) ?? $t;

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    }

    public static function words(?string $html): int
    {
        $t = self::plain($html);

        return $t === '' ? 0 : count(preg_split('/[\s\x{200c}\x{200f}]+/u', $t, -1, PREG_SPLIT_NO_EMPTY));
    }

    /** زمان مطالعه به دقیقه (۲۰۰ کلمه در دقیقه) */
    public static function readingMinutes(?string $html): int
    {
        return max(1, (int) ceil(self::words($html) / 200));
    }
}
