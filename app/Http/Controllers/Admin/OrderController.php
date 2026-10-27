<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $this->filtered($request)->latest()->paginate(20)->withQueryString();

        $counts = Order::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('admin.dash.orders.index', [
            'orders' => $orders,
            'statuses' => Order::statuses(),
            'types' => Order::types(),
            'counts' => $counts,
            'filters' => $request->only(['q', 'status', 'type', 'from', 'to']),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'lead', 'events.user']);

        return view('admin.dash.orders.show', [
            'order' => $order,
            'statuses' => Order::statuses(),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::statuses()))],
        ]);

        $from = $order->status;
        $order->update($data);

        if ($from !== $data['status']) {
            $order->events()->create([
                'user_id' => $request->user()->id,
                'type' => 'status',
                'from' => $from,
                'to' => $data['status'],
            ]);
        }

        // وضعیت لید مرتبط را هم هماهنگ نگه می‌داریم
        if ($order->lead && $data['status'] !== 'new') {
            $order->lead->update(['is_contacted' => true, 'needs_followup' => false]);
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok', 'label' => $order->status_label]);
        }

        return back()->with('success', 'وضعیت سفارش '.$order->code.' به «'.$order->status_label.'» تغییر کرد.');
    }

    public function updateNote(Request $request, Order $order)
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $changed = ($order->admin_note ?? '') !== ($data['admin_note'] ?? '');
        $order->update($data);

        if ($changed && filled($data['admin_note'] ?? null)) {
            $order->events()->create([
                'user_id' => $request->user()->id,
                'type' => 'note',
                'note' => \Illuminate\Support\Str::limit($data['admin_note'], 300),
            ]);
        }

        return back()->with('success', 'یادداشت ذخیره شد.');
    }

    public function destroy(Order $order)
    {
        $order->delete(); // فایل پیوست در رویداد deleting پاک می‌شود

        return redirect()->route('panel.orders.index')->with('success', 'سفارش حذف شد.');
    }

    public function attachment(Order $order)
    {
        abort_unless($order->attachment_path && Storage::disk('local')->exists($order->attachment_path), 404);

        return Storage::disk('local')->download(
            $order->attachment_path,
            $order->attachment_name ?: basename($order->attachment_path)
        );
    }

    /** خروجی CSV (UTF-8 با BOM برای اکسل) از همان فیلترهای لیست */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->latest();
        $filename = 'orders-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['کد', 'تاریخ', 'نوع', 'نام', 'موبایل', 'ایمیل', 'بودجه', 'زمان‌بندی', 'امکانات', 'زمان تماس', 'وضعیت', 'توضیحات', 'یادداشت', 'utm_source', 'utm_medium', 'utm_campaign']);

            $query->chunk(200, function ($chunk) use ($out) {
                foreach ($chunk as $o) {
                    fputcsv($out, array_map([$this, 'csvSafe'], [
                        $o->code,
                        $o->created_at?->format('Y-m-d H:i'),
                        $o->type_label,
                        $o->name,
                        $o->mobile,
                        $o->email,
                        $o->budget_label,
                        $o->deadline_label,
                        implode('، ', $o->feature_labels),
                        $o->contact_time_label,
                        $o->status_label,
                        $o->description,
                        $o->admin_note,
                        $o->utm_source,
                        $o->utm_medium,
                        $o->utm_campaign,
                    ]));
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request)
    {
        return Order::query()
            ->search($request->input('q'))
            ->status($request->input('status'))
            ->ofType($request->input('type'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')));
    }

    /** جلوگیری از CSV/Formula injection */
    private function csvSafe($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
