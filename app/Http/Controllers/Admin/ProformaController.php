<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proforma;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProformaController extends Controller
{
    public const STATUSES = [
        'draft' => 'پیش‌نویس',
        'sent' => 'ارسال شده',
        'accepted' => 'تأیید شده',
        'paid' => 'پرداخت شده',
    ];

    public const TONES = ['draft' => 'slate', 'sent' => 'amber', 'accepted' => 'sky', 'paid' => 'mint'];

    public function index(Request $request)
    {
        $status = $request->input('status');

        $proformas = Proforma::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($request->input('q'))).'%';
                $q->where(fn ($w) => $w->where('project_title', 'like', $like)->orWhere('client_name', 'like', $like)
                    ->orWhere('client_email', 'like', $like)->orWhere('client_phone', 'like', $like));
            })
            ->when(array_key_exists((string) $status, self::STATUSES), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $agg = Proforma::selectRaw('status, count(*) as c, coalesce(sum(total_amount),0) as t')->groupBy('status')->get()->keyBy('status');

        return view('admin.dash.proformas.index', [
            'proformas' => $proformas,
            'statuses' => self::STATUSES,
            'tones' => self::TONES,
            'agg' => $agg,
            'filters' => ['q' => (string) $request->input('q', ''), 'status' => (string) $status],
        ]);
    }

    public function create()
    {
        return view('admin.dash.proformas.create', [
            'proforma' => new Proforma(['status' => 'sent', 'project_title' => '']),
            'statuses' => self::STATUSES,
            'catalog' => $this->catalog(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);

        $proforma = Proforma::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->findUserId($data['client_email'] ?? null, $data['client_phone'] ?? null),
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'] ?? null,
            'client_phone' => $data['client_phone'] ?? null,
            'project_title' => $data['project_title'],
            'items' => $data['items'],
            'total_amount' => collect($data['items'])->sum('price'),
            'status' => $data['status'],
        ]);

        $msg = 'پیش‌فاکتور با موفقیت صادر شد.';
        if ($proforma->status !== 'draft' && $proforma->client_email) {
            $msg .= $this->sendInvoiceEmail($proforma) ? ' ایمیل برای مشتری ارسال شد.' : ' (ارسال ایمیل ناموفق بود؛ می‌توانید از لیست دوباره تلاش کنید.)';
        }

        return redirect()->route('panel.proformas.index')->with('success', $msg);
    }

    public function edit(Proforma $proforma)
    {
        return view('admin.dash.proformas.edit', [
            'proforma' => $proforma,
            'statuses' => self::STATUSES,
            'catalog' => $this->catalog(),
        ]);
    }

    public function update(Request $request, Proforma $proforma)
    {
        $data = $this->validated($request, false);

        $wasDraft = $proforma->status === 'draft';

        $proforma->update([
            'user_id' => $this->findUserId($data['client_email'] ?? null, $data['client_phone'] ?? null),
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'] ?? null,
            'client_phone' => $data['client_phone'] ?? null,
            'project_title' => $data['project_title'],
            'items' => $data['items'],
            'total_amount' => collect($data['items'])->sum('price'),
            'status' => $data['status'],
        ]);

        $msg = 'پیش‌فاکتور به‌روزرسانی شد.';
        // انتقال از پیش‌نویس به «ارسال شده» → ایمیل برای مشتری
        if ($wasDraft && $proforma->status !== 'draft' && $proforma->client_email) {
            $msg .= $this->sendInvoiceEmail($proforma) ? ' ایمیل برای مشتری ارسال شد.' : ' (ارسال ایمیل ناموفق بود.)';
        }

        return redirect()->route('panel.proformas.index')->with('success', $msg);
    }

    public function destroy(Proforma $proforma)
    {
        $proforma->delete();

        return redirect()->route('panel.proformas.index')->with('success', 'پیش‌فاکتور حذف شد.');
    }

    /** ارسال دوباره ایمیل پیش‌فاکتور */
    public function send(Proforma $proforma)
    {
        if (! $proforma->client_email) {
            return back()->with('error', 'برای این پیش‌فاکتور ایمیلی ثبت نشده است.');
        }

        if ($proforma->status === 'draft') {
            $proforma->update(['status' => 'sent']);
        }

        return $this->sendInvoiceEmail($proforma)
            ? back()->with('success', 'ایمیل پیش‌فاکتور برای '.$proforma->client_email.' ارسال شد.')
            : back()->with('error', 'ارسال ایمیل ناموفق بود. تنظیمات ایمیل (MAIL_*) را بررسی کنید.');
    }

    /* -------------------------------------------------------------- */

    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'project_title' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255', 'required_without:client_phone'],
            'client_phone' => ['nullable', 'string', 'max:30', 'required_without:client_email'],
            'status' => [$creating ? 'nullable' : 'required', Rule::in(array_keys(self::STATUSES))],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.price' => ['required', 'numeric', 'min:0', 'max:99999999999'],
        ], [
            'items.required' => 'حداقل یک ردیف خدمت لازم است.',
            'items.min' => 'حداقل یک ردیف خدمت لازم است.',
            'items.*.title.required' => 'عنوان همه ردیف‌ها را وارد کنید.',
            'items.*.price.required' => 'قیمت همه ردیف‌ها را وارد کنید.',
            'items.*.price.numeric' => 'قیمت باید عدد باشد.',
            'client_email.required_without' => 'ایمیل یا شماره تماس مشتری را وارد کنید.',
            'client_phone.required_without' => 'ایمیل یا شماره تماس مشتری را وارد کنید.',
        ]);

        $data['status'] = $data['status'] ?? 'sent';
        $data['items'] = collect($data['items'])->values()->map(fn ($i) => [
            'title' => $i['title'],
            'description' => $i['description'] ?? null,
            'price' => (int) round((float) $i['price']),
        ])->all();

        return $data;
    }

    /** کاتالوگ خدمات (config/aron_services.php) برای «افزودن سریع» در سازنده */
    private function catalog(): array
    {
        return collect(config('aron_services.catalog', []))->map(fn ($g) => [
            'label' => $g['label'],
            'items' => collect($g['items'])->map(fn ($i) => ['title' => $i['title'], 'price' => $i['price']])->values()->all(),
        ])->values()->all();
    }

    private function findUserId($email, $phone): ?int
    {
        if (! empty($email) && ($id = User::where('email', $email)->value('id'))) {
            return $id;
        }

        if (! empty($phone)) {
            return DB::table('user_profiles')->where('mobile', $phone)->value('user_id');
        }

        return null;
    }

    private function sendInvoiceEmail(Proforma $proforma): bool
    {
        try {
            $url = route('proforma.show', $proforma->uuid);

            Mail::send('emails.invoice', ['url' => $url], function ($m) use ($proforma) {
                $m->to($proforma->client_email)->subject('پیش‌فاکتور: '.$proforma->project_title);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error("Email Error Proforma #{$proforma->id}: ".$e->getMessage());

            return false;
        }
    }
}
