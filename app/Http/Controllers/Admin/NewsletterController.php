<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    private const ALLOWED_TAGS = '<p><br><a><strong><b><em><i><u><s><ul><ol><li><h1><h2><h3><h4><blockquote><hr><span><div><img><table><thead><tbody><tr><td><th>';

    public const STATES = [
        'confirmed' => 'تأییدشده',
        'pending' => 'منتظر تأیید',
        'unsubscribed' => 'لغو عضویت',
    ];

    public function index(Request $request)
    {
        $subscribers = $this->filtered($request)->latest()->paginate(20)->withQueryString();

        return view('admin.dash.newsletter.index', [
            'subscribers' => $subscribers,
            'counts' => [
                'all' => Newsletter::count(),
                'confirmed' => Newsletter::confirmed()->count(),
                'pending' => Newsletter::pending()->count(),
                'unsubscribed' => Newsletter::unsubscribed()->count(),
            ],
            'filters' => ['q' => (string) $request->input('q', ''), 'status' => (string) $request->input('status', '')],
            'states' => self::STATES,
        ]);
    }

    /** فعال/غیرفعال کردن (بدون حذف). غیرفعال‌سازی = لغو عضویت. */
    public function toggleStatus(Newsletter $newsletter)
    {
        if ($newsletter->is_active) {
            $newsletter->update(['is_active' => false, 'unsubscribed_at' => now()]);
            $msg = 'عضویت غیرفعال شد و دیگر خبرنامه‌ای برای این ایمیل ارسال نمی‌شود.';
        } else {
            $newsletter->update([
                'is_active' => true,
                'confirmed_at' => $newsletter->confirmed_at ?? now(),
                'unsubscribed_at' => null,
            ]);
            $msg = 'عضویت فعال شد.';
        }

        return back()->with('success', $msg);
    }

    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();

        return back()->with('success', 'ایمیل مورد نظر از لیست حذف شد.');
    }

    /** خروجی CSV (UTF-8 با BOM) از همان فیلترهای لیست */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->latest();

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ایمیل', 'وضعیت', 'تاریخ عضویت', 'تاریخ تأیید', 'تاریخ لغو']);
            $query->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $s) {
                    fputcsv($out, array_map([$this, 'csvSafe'], [
                        $s->email,
                        self::STATES[$s->state],
                        $s->created_at?->format('Y-m-d H:i'),
                        $s->confirmed_at?->format('Y-m-d H:i'),
                        $s->unsubscribed_at?->format('Y-m-d H:i'),
                    ]));
                }
            });
            fclose($out);
        }, 'newsletter-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function compose()
    {
        return view('admin.dash.newsletter.compose', ['recipients' => Newsletter::confirmed()->count()]);
    }

    /** ارسال گروهی (صف). «ارسال آزمایشی» فقط برای ایمیل خود ادمین می‌رود. */
    public function send(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:190'],
            'content' => ['required', 'string', 'max:200000'],
            'test' => ['nullable', 'boolean'],
        ], [
            'subject.required' => 'موضوع خبرنامه را وارد کنید.',
            'content.required' => 'متن خبرنامه را وارد کنید.',
        ]);

        $html = strip_tags($data['content'], self::ALLOWED_TAGS);
        // جلوگیری از لینک/تصویر javascript: و رویدادهای inline
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/i', '$1=$2#$2', $html);

        if ($request->boolean('test')) {
            Mail::to($request->user()->email)->queue(new NewsletterMail('[آزمایشی] '.$data['subject'], $html, null));

            return back()->withInput()->with('success', 'نسخه آزمایشی برای '.$request->user()->email.' ارسال شد.');
        }

        $count = 0;
        Newsletter::confirmed()->orderBy('id')->chunkById(200, function ($subs) use ($data, $html, &$count) {
            foreach ($subs as $sub) {
                if (! $sub->token) {
                    $sub->forceFill(['token' => Str::random(40)])->save();
                }
                Mail::to($sub->email)->queue(new NewsletterMail($data['subject'], $html, route('newsletter.unsubscribe', $sub->token)));
                $count++;
            }
        });

        return redirect()->route('panel.newsletter.index')
            ->with('success', $count ? "ارسال خبرنامه برای {$count} نفر در صف قرار گرفت." : 'عضو تأییدشده‌ای برای ارسال وجود ندارد.');
    }

    private function filtered(Request $request)
    {
        $status = $request->input('status');

        return Newsletter::query()
            ->when($request->filled('q'), fn ($q) => $q->where('email', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($request->input('q'))).'%'))
            ->when($status === 'confirmed', fn ($q) => $q->confirmed())
            ->when($status === 'pending', fn ($q) => $q->pending())
            ->when($status === 'unsubscribed', fn ($q) => $q->unsubscribed());
    }

    private function csvSafe($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
