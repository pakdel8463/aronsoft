<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class Utm
{
    /** @return array{utm_source:?string,utm_medium:?string,utm_campaign:?string} */
    public static function from(Request $request): array
    {
        $stored = $request->hasSession() ? (array) $request->session()->get('utm', []) : [];
        $out = [];
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $key) {
            $v = $request->input($key) ?: ($stored[$key] ?? null);
            $out[$key] = is_string($v) && $v !== '' ? mb_substr($v, 0, 100) : null;
        }

        return $out;
    }

    public static function referrer(Request $request): ?string
    {
        $v = $request->input('referrer')
            ?: ($request->hasSession() ? $request->session()->get('first_referrer') : null)
            ?: $request->headers->get('referer');

        return $v ? mb_substr((string) $v, 0, 500) : null;
    }
}
