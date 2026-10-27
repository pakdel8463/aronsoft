<?php

namespace App\Http\Controllers;

use App\View\Seo;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketDepartment;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * ناحیهٔ مشتری: /tickets  (فقط مالک تیکت؛ برای غیرمالک ۴۰۴ برمی‌گردد تا وجود تیکت لو نرود)
 */
class TicketController extends Controller
{
    private const CLIENT_FILTERS = [
        'open' => 'باز',
        'answered' => 'پاسخ داده شده',
        'closed' => 'بسته‌شده',
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');
        $dept = (int) $request->query('department');

        $q = Ticket::query()->where('user_id', $user->id)->with('department');
        match ($status) {
            'open' => $q->whereIn('status', [Ticket::OPEN, Ticket::WAITING_ADMIN]),
            'answered' => $q->where('status', Ticket::WAITING_CUSTOMER),
            'closed' => $q->where('status', Ticket::CLOSED),
            default => null,
        };
        if ($dept > 0) {
            $q->where('department_id', $dept);
        }

        $tickets = $q->orderByDesc('client_unread')->orderByDesc('last_reply_at')->orderByDesc('id')
            ->paginate(10)->withQueryString();

        Seo::meta(['title' => 'تیکت‌های من', 'description' => 'پیگیری درخواست‌های پشتیبانی', 'canonical' => route('tickets.index'), 'noindex' => true]);

        return view('front.t-one.tickets.index', [
            'tickets' => $tickets,
            'filters' => self::CLIENT_FILTERS,
            'status' => isset(self::CLIENT_FILTERS[$status]) ? $status : null,
            'department' => $dept ?: null,
            'departments' => TicketDepartment::active()->ordered()->get(),
            'total' => Ticket::where('user_id', $user->id)->count(),
            'unread' => Ticket::unreadCountFor($user),
        ]);
    }

    public function create(Request $request)
    {
        // مهمان: به ورود هدایت می‌شود و پس از ورود به همین صفحه برمی‌گردد
        if (! $request->user()) {
            return redirect()->guest(route('login'))
                ->with('status', 'برای ثبت تیکت ابتدا وارد حساب کاربری شوید؛ اگر حساب ندارید ثبت‌نام کنید.');
        }

        Seo::meta(['title' => 'ثبت تیکت جدید', 'description' => 'درخواست پشتیبانی', 'canonical' => route('tickets.create'), 'noindex' => true]);

        return view('front.t-one.tickets.create', [
            'departments' => TicketDepartment::active()->ordered()->get(),
            'orders' => $this->ordersOf($request->user()),
            'rules' => $this->uploadHints(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'department_id' => ['required', Rule::exists('ticket_departments', 'id')->where('is_active', true)],
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])],
            'subject' => ['required', 'string', 'min:4', 'max:190'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
            'order_code' => ['nullable', 'string', 'max:30'],
        ] + $this->attachmentRules(), [], $this->attributes());

        $open = Ticket::where('user_id', $user->id)->notClosed()->count();
        if ($open >= (int) config('tickets.max_open_per_user', 10)) {
            return back()->withInput()->withErrors(['subject' => 'تعداد تیکت‌های باز شما به حد مجاز رسیده است؛ ابتدا تیکت‌های قبلی را ببندید.']);
        }

        $orderCode = null;
        if (! empty($data['order_code'])) {
            $orderCode = $this->ordersOf($user)->firstWhere('code', $data['order_code'])?->code;
            if (! $orderCode) {
                return back()->withInput()->withErrors(['order_code' => 'سفارش انتخاب‌شده معتبر نیست.']);
            }
        }

        $ticket = Ticket::openFor($user, [
            'department_id' => $data['department_id'],
            'priority' => $data['priority'],
            'subject' => $this->clean($data['subject']),
            'body' => $this->cleanBody($data['body']),
            'order_code' => $orderCode,
        ], $request->file('attachments', []), $request->ip());

        return redirect()->route('tickets.show', $ticket)->with('success', 'تیکت شما ثبت شد. پاسخ پشتیبانی را در همین صفحه (و از طریق ایمیل) دریافت می‌کنید.');
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->owned($request, $ticket);

        if ($ticket->client_unread) {
            Ticket::whereKey($ticket->id)->update(['client_unread' => false]);
            $ticket->client_unread = false;
        }

        $ticket->load('department', 'assignee:id,name');
        $messages = $ticket->messages()->visibleToClient()->with(['user:id,name', 'attachments'])->get();

        Seo::meta(['title' => $ticket->number.' — '.$ticket->subject, 'description' => 'گفتگوی تیکت', 'canonical' => route('tickets.show', $ticket), 'noindex' => true]);

        return $this->noindex(response()->view('front.t-one.tickets.show', [
            'ticket' => $ticket,
            'messages' => $messages,
            'canReply' => $request->user()->can('reply', $ticket),
            'canReopen' => $request->user()->can('reopen', $ticket),
            'canRate' => $request->user()->can('rate', $ticket),
            'rules' => $this->uploadHints(),
        ]));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $this->owned($request, $ticket);

        if ($ticket->isClosed()) {
            return back()->with('error', 'این تیکت بسته شده است؛ برای ادامه ابتدا آن را دوباره باز کنید.');
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:10000'],
        ] + $this->attachmentRules(), [], $this->attributes());

        $ticket->postMessage($request->user(), TicketMessage::CLIENT, $this->cleanBody($data['body']), $request->file('attachments', []));

        return redirect()->to(route('tickets.show', $ticket).'#last')->with('success', 'پاسخ شما ارسال شد.');
    }

    public function close(Request $request, Ticket $ticket)
    {
        $this->owned($request, $ticket);
        abort_unless($request->user()->can('close', $ticket), 403);

        $ticket->close($request->user());

        return redirect()->route('tickets.show', $ticket)->with('success', 'تیکت بسته شد. اگر مایلید به پشتیبانی امتیاز دهید.');
    }

    public function reopen(Request $request, Ticket $ticket)
    {
        $this->owned($request, $ticket);
        if (! $request->user()->can('reopen', $ticket)) {
            return back()->with('error', 'مهلت بازگشایی این تیکت گذشته است؛ لطفاً تیکت جدیدی ثبت کنید.');
        }

        $ticket->reopen($request->user());
        $ticket->notifyStaff($ticket->messages()->visibleToClient()->latest('id')->first(), false);

        return redirect()->to(route('tickets.show', $ticket).'#reply')->with('success', 'تیکت دوباره باز شد.');
    }

    public function rate(Request $request, Ticket $ticket)
    {
        $this->owned($request, $ticket);
        abort_unless($request->user()->can('rate', $ticket), 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'rating_comment' => ['nullable', 'string', 'max:500'],
        ], [], ['rating' => 'امتیاز', 'rating_comment' => 'توضیح']);

        DB::transaction(function () use ($ticket, $data, $request) {
            $ticket->forceFill([
                'rating' => $data['rating'],
                'rating_comment' => $this->clean((string) ($data['rating_comment'] ?? '')) ?: null,
                'rated_at' => now(),
            ])->save();
            $ticket->logEvent('rated', $request->user(), ['rating' => (int) $data['rating']]);
        });

        return redirect()->route('tickets.show', $ticket)->with('success', 'از بازخورد شما سپاسگزاریم.');
    }

    public function attachment(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        $this->owned($request, $ticket);
        abort_unless($request->user()->can('download', [$ticket, $attachment]), 404);

        return self::sendAttachment($attachment, $request->boolean('inline'));
    }

    /** پاسخ دانلود/نمایش امن پیوست (مشترک با پنل مدیریت). */
    public static function sendAttachment(TicketAttachment $attachment, bool $inline = false)
    {
        $disk = Storage::disk(config('tickets.attachments.disk'));
        abort_unless($disk->exists($attachment->path), 404);

        $headers = [
            'Content-Type' => $attachment->mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ];

        // فقط تصویرهای امن inline نمایش داده می‌شوند؛ بقیه همیشه دانلود
        if ($inline && $attachment->isImage()) {
            return $disk->response($attachment->path, $attachment->original_name, $headers, 'inline');
        }

        return $disk->download($attachment->path, $attachment->original_name, $headers);
    }

    /* ------------------------------------------------------------------ helpers */

    private function owned(Request $request, Ticket $ticket): void
    {
        // سوپرکاربر هم در ناحیهٔ مشتری فقط تیکت‌های خودش را می‌بیند (پنل مدیریت برای بقیه است)
        abort_unless($ticket->user_id === $request->user()->id, 404);
    }

    private function attachmentRules(): array
    {
        $cfg = config('tickets.attachments');
        $mimes = implode(',', array_keys($cfg['types']));

        return [
            'attachments' => ['nullable', 'array', 'max:'.$cfg['max_files']],
            'attachments.*' => ['file', 'max:'.$cfg['max_kb'], 'mimes:'.$mimes, function ($attr, $value, $fail) use ($cfg) {
                // بررسی نهایی نوع واقعی فایل (بر اساس محتوا)
                $mime = $value instanceof \Illuminate\Http\UploadedFile ? (string) $value->getMimeType() : '';
                $allowed = collect($cfg['types'])->flatten()->all();
                if (! in_array($mime, $allowed, true)) {
                    $fail('نوع فایل پیوست مجاز نیست.');
                }
            }],
        ];
    }

    private function attributes(): array
    {
        return [
            'department_id' => 'دپارتمان', 'priority' => 'اولویت', 'subject' => 'موضوع', 'body' => 'متن پیام',
            'order_code' => 'سفارش', 'attachments' => 'پیوست‌ها', 'attachments.*' => 'پیوست',
        ];
    }

    private function uploadHints(): array
    {
        $cfg = config('tickets.attachments');

        return [
            'max_files' => $cfg['max_files'],
            'max_mb' => round($cfg['max_kb'] / 1024),
            'exts' => collect(array_keys($cfg['types']))->reject(fn ($e) => $e === 'jpeg')->values()->all(),
        ];
    }

    /** سفارش‌های کاربر برای فیلد اختیاری «سفارش مرتبط». */
    private function ordersOf($user)
    {
        try {
            if (! Schema::hasTable('orders')) {
                return collect();
            }

            return DB::table('orders')->where('user_id', $user->id)->latest('id')->limit(30)->get(['code', 'type', 'created_at']);
        } catch (\Throwable) {
            return collect();
        }
    }

    private function clean(string $v): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($v)) ?? '');
    }

    /** متن پیام: حذف کاراکترهای کنترلی، یکسان‌سازی خط جدید؛ HTML هنگام نمایش escape می‌شود. */
    private function cleanBody(string $v): string
    {
        $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? '';
        $v = str_replace(["\r\n", "\r"], "\n", $v);

        return trim(preg_replace("/\n{4,}/", "\n\n\n", $v) ?? '');
    }
}
