<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketDepartment;
use Illuminate\Http\Request;

class TicketDepartmentController extends Controller
{
    public function index()
    {
        return view('admin.dash.tickets.departments', [
            'departments' => TicketDepartment::withCount('tickets')->ordered()->get(),
        ]);
    }

    public function store(Request $request)
    {
        TicketDepartment::create($this->data($request, 'create'));

        return redirect()->route('panel.tickets.departments')->with('success', 'دپارتمان افزوده شد.');
    }

    public function update(Request $request, TicketDepartment $department)
    {
        $department->update($this->data($request, 'edit-'.$department->id));

        return redirect()->route('panel.tickets.departments')->with('success', 'دپارتمان ذخیره شد.');
    }

    public function destroy(TicketDepartment $department)
    {
        if ($department->tickets()->exists()) {
            return back()->with('error', 'این دپارتمان تیکت دارد و قابل حذف نیست؛ به‌جای حذف آن را غیرفعال کنید.');
        }
        $department->delete();

        return redirect()->route('panel.tickets.departments')->with('success', 'دپارتمان حذف شد.');
    }

    private function data(Request $request, string $bag): array
    {
        $data = $request->validateWithBag($bag, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'between:0,65535'],
            'is_active' => ['nullable', 'boolean'],
        ], [], ['name' => 'نام', 'email' => 'ایمیل', 'description' => 'توضیح', 'sort_order' => 'ترتیب']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
