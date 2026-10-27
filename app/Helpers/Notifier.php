<?php

namespace App\Helpers;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Fire-and-forget notifications. A mail/Telegram failure must never break
 * the visitor's request, so everything is wrapped and logged.
 */
class Notifier
{
    public static function admin(Mailable $mail): bool
    {
        $to = config('mail.admin_address');

        return $to ? self::to($to, $mail) : false;
    }

    public static function to(?string $address, Mailable $mail): bool
    {
        if (! $address) {
            return false;
        }

        try {
            Mail::to($address)->send($mail);

            return true;
        } catch (Throwable $e) {
            Log::warning('Mail send failed: '.$e->getMessage(), ['to' => $address, 'mail' => $mail::class]);

            return false;
        }
    }

    public static function telegram(string $text): void
    {
        $token = config('services.telegram.bot_token');
        $chat = config('services.telegram.chat_id');
        if (! $token || ! $chat) {
            return;
        }

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chat,
                'text' => $text,
                'disable_web_page_preview' => true,
            ]);
        } catch (Throwable $e) {
            Log::warning('Telegram notify failed: '.$e->getMessage());
        }
    }

    /** Route admin alerts through the configurable SMS/email/database system. */
    public static function adminAlert(string $subject, string $heading, array $rows, ?string $url = null): void
    {
        \App\Services\AdminNotifications::alert($subject, $heading, $rows, $url);
    }
}
