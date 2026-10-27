<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * First-touch attribution: remembers utm_* params (and the external referrer)
 * in the session so forms submitted later can attach them.
 */
class CaptureUtm
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET') && ! $request->expectsJson() && $request->hasSession()) {
            $found = [];
            foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $key) {
                $v = $request->query($key);
                if (is_string($v) && $v !== '') {
                    $found[$key] = mb_substr($v, 0, 100);
                }
            }
            if ($found) {
                $request->session()->put('utm', $found);
            }

            if (! $request->session()->has('first_referrer')) {
                $ref = (string) $request->headers->get('referer', '');
                $refHost = parse_url($ref, PHP_URL_HOST);
                if ($refHost && $refHost !== $request->getHost()) {
                    $request->session()->put('first_referrer', mb_substr($ref, 0, 500));
                }
            }
        }

        return $next($request);
    }
}
