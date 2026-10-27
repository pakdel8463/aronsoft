<?php

namespace App\Http\Controllers;

use App\Helpers\Notifier;
use App\Helpers\Utm;
use App\Http\Requests\LeadRequest;
use App\Models\Lead;
use Throwable;

/**
 * درخواست تماس سریع («تماس بگیرید»): فقط موبایل الزامی است.
 */
class LeadController extends Controller
{
    public function store(LeadRequest $request)
    {
        $ok = 'درخواست شما ثبت شد. کارشناسان ما در اسرع وقت با شما تماس می‌گیرند.';

        if ($request->isSpam()) {
            return $this->respond($request, $ok);
        }

        try {
            $lead = Lead::create([
                'name' => $request->input('name'),
                'mobile' => $request->input('mobile'),
                'email' => $request->input('email'),
                'company' => $request->input('company'),
                'request_type' => $request->input('request_type') ?: 'درخواست تماس',
                'message' => $request->input('message'),
                'page' => $request->input('page') ?: parse_url((string) url()->previous(), PHP_URL_PATH),
                'referrer' => Utm::referrer($request),
                'ip' => $request->ip(),
                'is_contacted' => false,
                'needs_followup' => true,
                ...Utm::from($request),
            ]);
        } catch (Throwable $e) {
            report($e);

            return $this->respond($request, 'ثبت درخواست با خطا مواجه شد. لطفاً دوباره تلاش کنید.', false, 500);
        }

        Notifier::adminAlert(
            'درخواست تماس جدید: '.$lead->mobile,
            'درخواست تماس جدید',
            [
                'نام' => $lead->name,
                'موبایل' => $lead->mobile,
                'ایمیل' => $lead->email,
                'نوع درخواست' => $lead->request_type,
                'پیام' => $lead->message,
                'صفحه' => $lead->page,
            ],
            route('panel.leads.show', $lead->id),
        );

        return $this->respond($request, $ok);
    }

    private function respond(LeadRequest $request, string $message, bool $success = true, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => $success ? 'success' : 'error', 'message' => $message], $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
