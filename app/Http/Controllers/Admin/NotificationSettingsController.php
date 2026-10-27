<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AdminNotifications as N;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
class NotificationSettingsController extends Controller
{
    private function authorizeSuper(): void { abort_unless(auth()->user()?->isSuperAdmin(), 403); }
    public function index() { $this->authorizeSuper(); return view('admin.notifications.settings', ['settings' => N::settings(), 'events' => N::EVENTS, 'hasSmsKey' => N::secret('sms_api_key') !== '', 'hasSmtpPassword' => N::secret('smtp_password') !== '']); }
    public function update(Request $request)
    {
        $this->authorizeSuper();
        $data = $request->validate(['sms_recipients' => 'nullable|string|max:2000', 'sms_sender' => 'nullable|string|max:40', 'sms_api_key' => 'nullable|string|max:500', 'email_recipients' => 'nullable|string|max:2000', 'smtp_host' => 'nullable|string|max:255', 'smtp_port' => 'nullable|integer|between:1,65535', 'smtp_username' => 'nullable|string|max:255', 'smtp_password' => 'nullable|string|max:500', 'smtp_encryption' => 'required|in:tls,ssl,none', 'smtp_from_address' => 'nullable|email|max:255', 'smtp_from_name' => 'nullable|string|max:255', 'events' => 'nullable|array']);
        $settings = N::settings();
        foreach (['enabled','sms_enabled','email_enabled','admin_enabled'] as $key) $settings[$key] = $request->boolean($key);
        foreach (['sms_recipients','sms_sender','email_recipients','smtp_host','smtp_port','smtp_username','smtp_encryption','smtp_from_address','smtp_from_name'] as $key) $settings[$key] = $data[$key] ?? '';
        foreach (N::EVENTS as $event => $label) foreach (['sms','email','admin'] as $channel) $settings['events'][$event][$channel] = $request->boolean("events.$event.$channel");
        foreach (['sms_api_key','smtp_password'] as $key) if ($request->filled($key)) $settings[$key] = Crypt::encryptString($data[$key]);
        Setting::query()->updateOrCreate(['key' => 'admin_notifications'], ['value' => json_encode($settings, JSON_UNESCAPED_UNICODE)]);
        return back()->with('success', 'تنظیمات اعلان‌ها ذخیره شد.');
    }
    public function history() { $this->authorizeSuper(); return view('admin.notifications.history', ['deliveries' => DB::table('admin_notification_deliveries')->latest()->paginate(30)]); }
    public function markRead(string $id) { abort_unless(auth()->user()?->isSuperAdmin(),403); auth()->user()->notifications()->whereKey($id)->update(['read_at' => now()]); return back(); }
    public function test(Request $request)
    {
        $this->authorizeSuper();
        $data = $request->validate(['channel' => 'required|in:sms,email', 'recipient' => 'required|string|max:255']);
        $settings = N::settings(); $channel = $data['channel']; $recipient = trim($data['recipient']);
        if (! $settings['enabled'] || ! $settings[$channel.'_enabled']) return back()->with('error', 'ابتدا سیستم و کانال موردنظر را فعال کنید.');
        if ($channel === 'email' ? !filter_var($recipient,FILTER_VALIDATE_EMAIL) : !preg_match('/^\+?[0-9]{10,15}$/', $recipient)) return back()->with('error', 'گیرنده معتبر نیست.');
        $id = DB::table('admin_notification_deliveries')->insertGetId(['event' => 'test', 'channel' => $channel, 'recipient' => $recipient, 'status' => 'queued', 'created_at' => now(), 'updated_at' => now()]);
        // Tests are delivered independently from event toggles.
        \App\Jobs\SendAdminAlert::dispatch($id, 'تست آرون سافت', 'پیام آزمایشی آرون سافت', ['وضعیت' => 'تست سیستم اعلان'], null);
        return back()->with('success', 'پیام آزمایشی در صف ارسال قرار گرفت.');
    }
}
