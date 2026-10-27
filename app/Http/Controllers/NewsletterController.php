<?php

namespace App\Http\Controllers;

use App\Helpers\Notifier;
use App\Helpers\SeoHelper;
use App\Http\Requests\NewsletterRequest;
use App\Mail\NewsletterConfirmMail;
use App\Models\Newsletter;
use Illuminate\Support\Str;

/**
 * عضویت دو مرحله‌ای (double opt-in):
 *  store → ایمیل تأیید  →  confirm → فعال  →  unsubscribe → غیرفعال
 */
class NewsletterController extends Controller
{
    public function store(NewsletterRequest $request)
    {
        if ($request->isSpam()) {
            return response()->json(['message' => 'ایمیل تأیید برای شما ارسال شد.']);
        }

        $email = $request->input('email');
        $subscriber = Newsletter::where('email', $email)->first();

        if ($subscriber && $subscriber->is_active) {
            return response()->json(['message' => 'این ایمیل قبلاً در خبرنامه عضو شده است.']);
        }

        if (! $subscriber) {
            $subscriber = Newsletter::create([
                'email' => $email,
                'token' => Str::random(40),
                'is_active' => false,
            ]);
        } else {
            $subscriber->forceFill([
                'token' => $subscriber->token ?: Str::random(40),
                'unsubscribed_at' => null,
            ])->save();
        }

        if (! Notifier::to($subscriber->email, new NewsletterConfirmMail($subscriber))) {
            return response()->json([
                'message' => 'ارسال ایمیل تأیید با مشکل مواجه شد. لطفاً کمی بعد دوباره تلاش کنید.',
            ], 503);
        }

        return response()->json([
            'message' => 'ایمیل تأیید ارسال شد. لطفاً صندوق ایمیل (و پوشه اسپم) را بررسی و عضویت را تأیید کنید.',
        ]);
    }

    public function confirm(string $token)
    {
        $subscriber = $this->findByToken($token);

        if (! $subscriber) {
            return $this->notice('پیوند نامعتبر است', 'این پیوند تأیید نامعتبر یا منقضی شده است. لطفاً دوباره در خبرنامه عضو شوید.', false, 404);
        }

        if (! $subscriber->is_active) {
            $subscriber->forceFill([
                'is_active' => true,
                'confirmed_at' => now(),
                'unsubscribed_at' => null,
            ])->save();
        }

        return $this->notice('عضویت شما تأیید شد', 'از این پس تازه‌ترین مطالب آرون سافت را در ایمیل خود دریافت می‌کنید.');
    }

    public function unsubscribe(string $token)
    {
        $subscriber = $this->findByToken($token);

        if (! $subscriber) {
            return $this->notice('پیوند نامعتبر است', 'این پیوند لغو عضویت نامعتبر است.', false, 404);
        }

        $subscriber->forceFill([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ])->save();

        return $this->notice('عضویت شما لغو شد', 'دیگر ایمیلی از خبرنامه برای شما ارسال نخواهد شد.');
    }

    private function findByToken(string $token): ?Newsletter
    {
        if (strlen($token) < 20) {
            return null;
        }

        return Newsletter::where('token', $token)->first();
    }

    private function notice(string $title, string $message, bool $ok = true, int $status = 200)
    {
        SeoHelper::set(['title' => $title, 'description' => $message]);

        return $this->noindex(response()->view('front.t-one.contact.notice', compact('title', 'message', 'ok'), $status));
    }
}
