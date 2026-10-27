<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCannedResponse;
use App\Models\TicketDepartment;
use Illuminate\Http\Request;

/** پاسخ‌های آمادهٔ پشتیبانی (canned responses). */
class TicketCannedResponseController extends Controller
{
    public function index()
    {
        return view('admin.dash.tickets.canned', [
            'responses' => TicketCannedResponse::with('department:id,name')->orderBy('title')->get(),
            'departments' => TicketDepartment::ordered()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        TicketCannedResponse::create($this->data($request, 'create'));

        return redirect()->route('panel.tickets.canned')->with('success', 'پاسخ آماده افزوده شد.');
    }

    public function update(Request $request, TicketCannedResponse $canned)
    {
        $canned->update($this->data($request, 'edit-'.$canned->id));

        return redirect()->route('panel.tickets.canned')->with('success', 'پاسخ آماده ذخیره شد.');
    }

    public function destroy(TicketCannedResponse $canned)
    {
        $canned->delete();

        return redirect()->route('panel.tickets.canned')->with('success', 'پاسخ آماده حذف شد.');
    }

    private function data(Request $request, string $bag): array
    {
        $data = $request->validateWithBag($bag, [
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:5000'],
            'department_id' => ['nullable', 'exists:ticket_departments,id'],
            'is_active' => ['nullable', 'boolean'],
        ], [], ['title' => 'عنوان', 'body' => 'متن', 'department_id' => 'دپارتمان']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['department_id'] = $data['department_id'] ?? null;

        return $data;
    }
}
