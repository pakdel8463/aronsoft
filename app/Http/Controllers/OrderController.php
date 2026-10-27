<?php

namespace App\Http\Controllers;

use App\Helpers\Notifier;
use App\Helpers\SeoHelper;
use App\Helpers\Utm;
use App\Http\Requests\OrderRequest;
use App\Mail\OrderReceivedMail;
use App\Models\Lead;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * فرم سفارش / مشاوره چندمرحله‌ای: /order
 */
class OrderController extends Controller
{
    public function create(Request $request)
    {
        SeoHelper::set([
            'title' => 'ثبت سفارش و مشاوره رایگان',
            'description' => 'پروژه خود را در سه مرحله ساده برای آرون سافت ارسال کنید؛ کارشناسان ما با شما تماس می‌گیرند و برآورد هزینه و زمان را ارائه می‌دهند.',
            'canonical' => route('order.create'),
        ]);

        $cfg = config('aron_services.order');

        return view('front.t-one.order.create', [
            'types' => $cfg['types'],
            'budgets' => $cfg['budgets'],
            'deadlines' => $cfg['deadlines'],
            'contactTimes' => $cfg['contact_times'],
            'preselect' => array_key_exists((string) $request->query('type'), $cfg['types']) ? $request->query('type') : null,
        ]);
    }

    public function store(OrderRequest $request)
    {
        if ($request->isSpam()) {
            // ربات: ظاهراً موفق، بدون ذخیره
            return $this->done($request, route('order.create'), 'ARN-000000');
        }

        $path = null;
        $originalName = null;
        if ($file = $request->file('attachment')) {
            $originalName = Str::limit(preg_replace('/[^\p{L}\p{N}\.\-_ ]/u', '', $file->getClientOriginalName()), 150, '');
            $path = $file->store('orders/'.now()->format('Y/m'), 'local');
        }

        try {
            $order = DB::transaction(function () use ($request, $path, $originalName) {
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'type' => $request->input('type'),
                    'budget' => $request->input('budget'),
                    'deadline' => $request->input('deadline'),
                    'features' => array_values($request->input('features', [])),
                    'description' => $request->input('description'),
                    'attachment_path' => $path,
                    'attachment_name' => $originalName,
                    'name' => $request->input('name'),
                    'mobile' => $request->input('mobile'),
                    'email' => $request->input('email'),
                    'contact_time' => $request->input('contact_time'),
                    'consented_at' => now(),
                    'status' => 'new',
                    'ip' => $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 250),
                    'referrer' => Utm::referrer($request),
                    ...Utm::from($request),
                ]);

                // هر سفارش یک لید هم می‌سازد تا در صف پیگیری تیم فروش دیده شود
                $lead = Lead::create([
                    'name' => $order->name,
                    'mobile' => $order->mobile,
                    'email' => $order->email,
                    'request_type' => 'سفارش: '.$order->type_label,
                    'message' => $this->leadSummary($order),
                    'page' => '/order',
                    'referrer' => $order->referrer,
                    'ip' => $order->ip,
                    'is_contacted' => false,
                    'needs_followup' => true,
                    'utm_source' => $order->utm_source,
                    'utm_medium' => $order->utm_medium,
                    'utm_campaign' => $order->utm_campaign,
                ]);

                $order->forceFill(['lead_id' => $lead->id])->save();

                return $order;
            });
        } catch (Throwable $e) {
            report($e);
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            $msg = 'ثبت درخواست با خطا مواجه شد. لطفاً دوباره تلاش کنید یا با ما تماس بگیرید.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $msg], 500);
            }

            return back()->withInput()->with('error', $msg);
        }

        // اعلان‌ها هرگز نباید کاربر را با خطا مواجه کنند
        Notifier::to($order->email, new OrderReceivedMail($order));
        Notifier::adminAlert(
            'سفارش جدید '.$order->code.' — '.$order->type_label,
            'سفارش / درخواست مشاوره جدید',
            [
                'کد' => $order->code,
                'نوع' => $order->type_label,
                'نام' => $order->name,
                'موبایل' => $order->mobile,
                'ایمیل' => $order->email,
                'بودجه' => $order->budget_label,
                'زمان‌بندی' => $order->deadline_label,
                'امکانات' => implode('، ', $order->feature_labels),
                'زمان تماس' => $order->contact_time_label,
                'توضیحات' => $order->description,
                'پیوست' => $order->attachment_name,
            ],
            $this->adminUrl($order),
        );

        return $this->done($request, $order->publicUrl(), $order->code);
    }

    public function show(Request $request, string $code)
    {
        $order = Order::where('code', strtoupper($code))->firstOrFail();

        // کد به‌تنهایی کافی نیست؛ توکن هم باید درست باشد
        abort_unless(hash_equals($order->access_token, (string) $request->query('t')), 404);

        SeoHelper::set(['title' => 'پیگیری سفارش '.$order->code, 'description' => 'وضعیت درخواست شما در آرون سافت']);
        \Artesaos\SEOTools\Facades\SEOMeta::setRobots('noindex, nofollow');

        return $this->noindex(response()->view('front.t-one.order.show', [
            'order' => $order,
            'justCreated' => (bool) $request->session()->pull('order_just_created'),
        ]));
    }

    private function done(OrderRequest $request, string $url, string $code)
    {
        $request->session()->flash('order_just_created', true);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'درخواست شما ثبت شد.',
                'code' => $code,
                'redirect' => $url,
            ]);
        }

        return redirect()->to($url);
    }

    private function leadSummary(Order $order): string
    {
        return collect([
            "کد سفارش: {$order->code}",
            'نوع: '.$order->type_label,
            $order->budget_label ? 'بودجه: '.$order->budget_label : null,
            $order->deadline_label ? 'زمان‌بندی: '.$order->deadline_label : null,
            $order->feature_labels ? 'امکانات: '.implode('، ', $order->feature_labels) : null,
            $order->contact_time_label ? 'زمان تماس: '.$order->contact_time_label : null,
            $order->description ? 'توضیحات: '.Str::limit($order->description, 800) : null,
        ])->filter()->implode("\n");
    }

    private function adminUrl(Order $order): ?string
    {
        try {
            return route('panel.orders.show', $order->id);
        } catch (Throwable) {
            return null;
        }
    }
}
