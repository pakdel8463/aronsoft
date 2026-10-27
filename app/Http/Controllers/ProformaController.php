<?php

namespace App\Http\Controllers;

use App\Helpers\Notifier;
use App\Helpers\ServiceCatalog;
use App\Helpers\Fa;
use App\Http\Requests\ProformaRequest;
use App\Mail\InvoiceSent;
use App\Models\Proforma;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ProformaController extends Controller
{
    public function show($uuid)
    {
        $proforma = Proforma::where('uuid', $uuid)->firstOrFail();

        return $this->noindex(response()->view('front.t-one.proformas.show', compact('proforma')));
    }

    public function store(ProformaRequest $request)
    {
        if ($request->isSpam()) {
            return response()->json(['status' => 'success', 'message' => 'پیش‌فاکتور شما صادر شد.']);
        }

        // قیمت‌ها هرگز از کلاینت خوانده نمی‌شوند؛ فقط شناسه/عنوان آیتم‌ها
        // با کاتالوگ سرور (config/aron_services.php) تطبیق داده می‌شود.
        $resolved = ServiceCatalog::resolve($request->input('items', []));

        try {
            $userId = null;
            if ($request->filled('client_email')) {
                $userId = User::where('email', $request->client_email)->value('id');
            }

            $proforma = DB::transaction(fn () => Proforma::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'client_name' => $request->input('client_name') ?: 'کاربر مهمان',
                'client_email' => $request->input('client_email'),
                'client_phone' => $request->input('client_phone'),
                'project_title' => 'سفارش از سایت (محاسبه‌گر آنلاین)',
                'items' => $resolved['items'],
                'total_amount' => $resolved['total'],
                'status' => 'sent',
            ]));
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'صدور پیش‌فاکتور با خطا مواجه شد. لطفاً دوباره تلاش کنید.',
            ], 500);
        }

        $url = $proforma->publicUrl();

        // ایمیل کمکی است؛ خطای آن نباید ثبت پیش‌فاکتور را خراب کند
        $mailed = $proforma->client_email
            ? Notifier::to($proforma->client_email, new InvoiceSent($url, $proforma->project_title))
            : false;

        Notifier::adminAlert(
            'پیش‌فاکتور جدید از محاسبه‌گر آنلاین',
            'پیش‌فاکتور جدید صادر شد',
            [
                'نام' => $proforma->client_name,
                'موبایل' => $proforma->client_phone,
                'ایمیل' => $proforma->client_email,
                'مبلغ کل' => Fa::money($proforma->total_amount).' تومان',
                'آیتم‌ها' => collect($proforma->items)->pluck('title')->implode("\n"),
            ],
            $url,
        );

        return response()->json([
            'status' => 'success',
            'message' => $mailed
                ? 'پیش‌فاکتور صادر و به ایمیل شما ارسال شد.'
                : 'پیش‌فاکتور شما صادر شد.',
            'total' => (int) $proforma->total_amount,
            'redirect' => $url,
        ]);
    }
}
