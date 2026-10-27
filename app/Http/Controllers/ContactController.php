<?php

namespace App\Http\Controllers;

use App\Helpers\Notifier;
use App\Helpers\SeoHelper;
use App\Helpers\Utm;
use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactController extends Controller
{
    public function index()
    {
        SeoHelper::set([
            'title' => 'تماس با ما',
            'description' => 'ارتباط با تیم آرون سافت برای مشاوره، سفارش پروژه و پشتیبانی. تلفن ۰۲۱-۲۲۴۵۱۹۱۲، تهران، بلوار ارتش.',
            'canonical' => route('contact'),
        ]);

        return view('front.t-one.contact.contact', [
            'subjects' => config('aron_services.contact_subjects'),
        ]);
    }

    public function store(ContactRequest $request)
    {
        $ok = 'پیام شما ثبت شد. کارشناسان ما در اولین فرصت با شما تماس می‌گیرند.';

        // honeypot: ربات‌ها را بی‌سروصدا رد می‌کنیم
        if ($request->isSpam()) {
            return $this->respond($request, $ok);
        }

        $data = $request->validated();
        unset($data['captcha']);

        try {
            $message = ContactMessage::create([
                ...$data,
                'status' => 'new',
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 250),
                'referrer' => Utm::referrer($request),
                'page_url' => url()->previous(),
                ...Utm::from($request),
            ]);
        } catch (Throwable $e) {
            report($e);

            return $this->respond($request, 'متأسفانه ثبت پیام با خطا مواجه شد. لطفاً دوباره تلاش کنید یا تماس بگیرید.', false, 500);
        }

        Notifier::adminAlert(
            'پیام جدید از فرم تماس: '.($message->subject ?: 'بدون موضوع'),
            'پیام جدید از صفحه تماس با ما',
            [
                'نام' => $message->name,
                'موبایل' => $message->mobile,
                'ایمیل' => $message->email,
                'موضوع' => $message->subject,
                'پیام' => $message->message,
                'منبع' => collect([$message->utm_source, $message->utm_medium, $message->utm_campaign])->filter()->implode(' / '),
            ],
            $this->adminUrl($message->id),
        );

        return $this->respond($request, $ok);
    }

    private function adminUrl(int $id): ?string
    {
        try {
            return route('panel.contact-messages.show', $id);
        } catch (Throwable $e) {
            Log::debug($e->getMessage());

            return null;
        }
    }

    private function respond(ContactRequest $request, string $message, bool $success = true, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => $success ? 'success' : 'error', 'message' => $message], $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
