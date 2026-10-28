<?php

namespace App\Support;

/** Iranian national code (کد ملی) checksum validation. */
class NationalCode
{
    public static function valid(?string $code): bool
    {
        $code = (string) $code;
        if (! preg_match('/^\d{10}$/', $code) || preg_match('/^(\d)\1{9}$/', $code)) {
            return false;
        }
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $code[$i] * (10 - $i);
        }
        $r = $sum % 11;
        $check = (int) $code[9];

        return $r < 2 ? $check === $r : $check === 11 - $r;
    }
}
