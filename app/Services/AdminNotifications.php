<?php

namespace App\Services;

use App\Jobs\SendAdminAlert;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class AdminNotifications
{
    public const EVENTS = ['lead' => 'درخواست پروژه', 'contact' => 'پیام تماس', 'order' => 'سفارش', 'comment' => 'دیدگاه', 'proforma' => 'پیش‌فاکتور', 'other' => 'سایر رویدادها'];
    public const DEFAULTS = [
        'enabled' => false, 'sms_enabled' => false, 'email_enabled' => false, 'admin_enabled' => true,
        'sms_recipients' => '', 'sms_sender' => '', 'email_recipients' => '',
        'smtp_host' => '', 'smtp_port' => '587', 'smtp_username' => '', 'smtp_encryption' => 'tls',
        'smtp_from_address' => '', 'smtp_from_name' => 'Aron Soft',
        'events' => ['lead' => ['sms' => true, 'email' => true, 'admin' => true], 'contact' => ['sms' => true, 'email' => true, 'admin' => true], 'order' => ['sms' => true, 'email' => true, 'admin' => true], 'comment' => ['sms' => false, 'email' => false, 'admin' => true], 'proforma' => ['sms' => false, 'email' => true, 'admin' => true], 'other' => ['sms' => false, 'email' => false, 'admin' => true]],
    ];
    public static function settings(): array
    {
        $row = Setting::query()->where('key', 'admin_notifications')->value('value');
        $saved = json_decode((string) $row, true);
        return array_replace_recursive(self::DEFAULTS, is_array($saved) ? $saved : []);
    }
    public static function secret(string $key): string
    {
        $encrypted = self::settings()[$key] ?? null;
        if (! is_string($encrypted) || $encrypted === '') return '';
        try { return Crypt::decryptString($encrypted); } catch (\Throwable $e) { Log::warning('Invalid encrypted notification setting', ['key' => $key]); return ''; }
    }
    public static function event(string $heading): string
    {
        if (str_contains($heading, 'درخواست تماس')) return 'lead';
        if (str_contains($heading, 'تماس') || str_contains($heading, 'پیام')) return 'contact';
        if (str_contains($heading, 'سفارش')) return 'order';
        if (str_contains($heading, 'دیدگاه') || str_contains($heading, 'نظر')) return 'comment';
        if (str_contains($heading, 'پیش‌فاکتور')) return 'proforma';
        if (str_contains($heading, 'درخواست')) return 'lead';
        return 'other';
    }
    public static function alert(string $subject, string $heading, array $rows, ?string $url = null): void
    {
        try {
            $settings = self::settings();
            if (! $settings['enabled']) return;
            $event = self::event($heading);
            $channels = $settings['events'][$event] ?? [];
            if ($settings['admin_enabled'] && ($channels['admin'] ?? false)) {
                User::query()->where('is_superuser', true)->get()->each(function (User $user) use ($subject, $heading, $url, $event) {
                    $user->notify(new \App\Notifications\AdminEventNotification($subject, $heading, $url, $event));
                });
            }
            foreach (['sms', 'email'] as $channel) {
                if (! ($settings[$channel.'_enabled'] ?? false) || ! ($channels[$channel] ?? false)) continue;
                $recipients = preg_split('/[\s,;]+/', (string) $settings[$channel.'_recipients'], -1, PREG_SPLIT_NO_EMPTY);
                foreach (array_unique($recipients) as $recipient) {
                    if ($channel === 'sms' && ! preg_match('/^\+?[0-9]{10,15}$/', $recipient)) continue;
                    if ($channel === 'email' && ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;
                    $id = \Illuminate\Support\Facades\DB::table('admin_notification_deliveries')->insertGetId(['event' => $event, 'channel' => $channel, 'recipient' => $recipient, 'status' => 'queued', 'created_at' => now(), 'updated_at' => now()]);
                    SendAdminAlert::dispatch($id, $subject, $heading, $rows, $url)->onQueue('default');
                }
            }
        } catch (\Throwable $e) { report($e); }
    }
}
