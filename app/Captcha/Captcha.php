<?php

namespace App\Captcha;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * کپچای proof-of-work (سازگار با پروتکل ALTCHA).
 *  challenge = sha256(salt . number)  که number یک عدد مخفی تصادفی در [0, max_number] است.
 *  مرورگر number را با امتحان کردن پیدا می‌کند؛ سرور امضا، انقضا و یک‌بارمصرف بودن را بررسی می‌کند.
 */
final class Captcha
{
    public static function enabled(): bool
    {
        return (bool) config('captcha.enabled', true);
    }

    /** یک معمای تازه برای ویجت. */
    public static function challenge(): array
    {
        $max = max(1000, (int) config('captcha.max_number', 100000));
        $expires = now()->addMinutes(max(1, (int) config('captcha.ttl', 30)))->getTimestamp();
        $salt = Str::random(16).'?expires='.$expires;
        $number = random_int(0, $max);
        $challenge = hash('sha256', $salt.$number);

        return [
            'algorithm' => 'SHA-256',
            'challenge' => $challenge,
            'maxnumber' => $max,
            'salt' => $salt,
            'signature' => self::sign($challenge),
        ];
    }

    /**
     * بررسی جواب ارسالی (base64 از JSON: algorithm, challenge, number, salt, signature).
     * $consume: جواب درست را مصرف‌شده علامت می‌زند تا دوباره پذیرفته نشود.
     */
    public static function verify(?string $payload, bool $consume = true): bool
    {
        if (! is_string($payload) || $payload === '' || strlen($payload) > 2000) {
            return false;
        }
        $data = json_decode((string) base64_decode($payload, true), true);
        if (! is_array($data)) {
            return false;
        }
        foreach (['algorithm', 'challenge', 'number', 'salt', 'signature'] as $k) {
            if (! isset($data[$k]) || ! is_scalar($data[$k])) {
                return false;
            }
        }
        [$challenge, $salt, $number] = [(string) $data['challenge'], (string) $data['salt'], (string) $data['number']];

        if ($data['algorithm'] !== 'SHA-256' || ! ctype_digit($number)) {
            return false;
        }
        if (! hash_equals(self::sign($challenge), (string) $data['signature'])) {
            return false;
        }
        if (! hash_equals($challenge, hash('sha256', $salt.$number))) {
            return false;
        }
        if (! preg_match('/[?&]expires=(\d+)/', $salt, $m) || (int) $m[1] < time()) {
            return false;
        }
        if ($consume) {
            // Cache::add فقط وقتی کلید وجود ندارد موفق است → جواب تکراری رد می‌شود
            return Cache::add('captcha:used:'.$challenge, 1, max(60, (int) $m[1] - time() + 60));
        }

        return true;
    }

    /** برای تست‌ها: معما را می‌سازد و حل می‌کند. */
    public static function solvedPayload(): string
    {
        $c = self::challenge();
        for ($n = 0; $n <= $c['maxnumber']; $n++) {
            if (hash('sha256', $c['salt'].$n) === $c['challenge']) {
                break;
            }
        }

        return base64_encode(json_encode(['algorithm' => 'SHA-256', 'challenge' => $c['challenge'], 'number' => $n, 'salt' => $c['salt'], 'signature' => $c['signature']]));
    }

    private static function sign(string $challenge): string
    {
        return hash_hmac('sha256', $challenge, 'captcha|'.config('app.key'));
    }
}
