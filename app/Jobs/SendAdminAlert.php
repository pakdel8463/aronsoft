<?php
namespace App\Jobs;
use App\Services\AdminNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
class SendAdminAlert implements ShouldQueue
{
    use Queueable;
    public int $tries = 3;
    public int $timeout = 30;
    public function __construct(public int $deliveryId, public string $subject, public string $heading, public array $rows, public ?string $url) {}
    public function handle(): void
    {
        $delivery = DB::table('admin_notification_deliveries')->where('id', $this->deliveryId)->first();
        if (! $delivery || $delivery->status === 'sent') return;
        $settings = AdminNotifications::settings();
        if (! $settings['enabled'] || ! ($settings[$delivery->channel.'_enabled'] ?? false) || ($delivery->event !== 'test' && ! ($settings['events'][$delivery->event][$delivery->channel] ?? false))) { $this->mark('skipped'); return; }
        try {
            if ($delivery->channel === 'sms') {
                $key = AdminNotifications::secret('sms_api_key');
                if (! $key) throw new RuntimeException('Ghasedak API key is not configured');
                $body = $this->heading."\n".collect($this->rows)->map(fn ($v, $k) => "$k: $v")->implode("\n");
                $request = Http::timeout(12)->withHeaders(['apikey' => $key])->asForm();
                // Provider-required unsubscribe footer: always the final line, with nothing after it.
                $footer = 'لغو11';
                $content = rtrim($body);
                $maxContentLength = 900 - mb_strlen("\n".$footer);
                $content = rtrim(mb_substr($content, 0, $maxContentLength));
                $message = $content === '' ? $footer : $content."\n".$footer;
                $params = ['message' => $message, 'receptor' => $delivery->recipient];
                if ($settings['sms_sender'] !== '') $params['linenumber'] = $settings['sms_sender'];
                $res = $request->post('https://api.ghasedak.me/v2/sms/send/simple', $params);
                if (! $res->successful() || (is_numeric($res->json('result.code')) && (int) $res->json('result.code') !== 200) || (is_numeric($res->json('result')) && (int) $res->json('result') !== 200)) throw new RuntimeException('Ghasedak rejected request (HTTP '.$res->status().')');
            } elseif ($delivery->channel === 'email') {
                $host = $settings['smtp_host']; $username = $settings['smtp_username'];
                if (! $host || ! $settings['smtp_from_address']) throw new RuntimeException('SMTP is incomplete');
                config(['mail.mailers.aron_notifications' => ['transport' => 'smtp', 'host' => $host, 'port' => (int) $settings['smtp_port'], 'encryption' => $settings['smtp_encryption'] === 'none' ? null : $settings['smtp_encryption'], 'username' => $username, 'password' => AdminNotifications::secret('smtp_password'), 'timeout' => 15]]);
                Mail::purge('aron_notifications');
                Mail::mailer('aron_notifications')->to($delivery->recipient)->send((new \App\Mail\AdminNotificationMail($this->subject, $this->heading, $this->rows, $this->url))->from($settings['smtp_from_address'], $settings['smtp_from_name']));
            }
            $this->mark('sent');
        } catch (\Throwable $e) { $this->mark('failed', mb_substr($e->getMessage(), 0, 1000)); throw $e; }
    }
    private function mark(string $status, ?string $error = null): void { DB::table('admin_notification_deliveries')->where('id', $this->deliveryId)->update(['status' => $status, 'error' => $error, 'updated_at' => now()]); }
}
