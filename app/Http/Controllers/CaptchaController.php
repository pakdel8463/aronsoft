<?php

namespace App\Http\Controllers;

use App\Captcha\Captcha;

/** معمای تازه برای ویجت کپچا (بعد از ارسال ایجکسی یا منقضی شدن معمای قبلی). */
class CaptchaController extends Controller
{
    public function __invoke()
    {
        return response()->json(Captcha::challenge())->header('Cache-Control', 'no-store');
    }
}
