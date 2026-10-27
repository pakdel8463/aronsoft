<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LeadController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:leads-index')];
    }

    public function index(Request $request)
    {
        $status = $request->input('status');

        $leads = Lead::query()
            ->search($request->input('q'))
            ->when($status === 'pending', fn ($q) => $q->where('is_contacted', false))
            ->when($status === 'contacted', fn ($q) => $q->where('is_contacted', true))
            ->when($request->filled('type'), fn ($q) => $q->where('request_type', $request->input('type')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.dash.leads.index', [
            'leads' => $leads,
            'types' => Lead::whereNotNull('request_type')->where('request_type', '!=', '')->distinct()->orderBy('request_type')->pluck('request_type'),
            'counts' => [
                'all' => Lead::count(),
                'pending' => Lead::where('is_contacted', false)->count(),
                'contacted' => Lead::where('is_contacted', true)->count(),
            ],
            'filters' => $request->only(['q', 'status', 'type']),
        ]);
    }

    public function show(Lead $lead)
    {
        return view('admin.dash.leads.show', compact('lead'));
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $data = $request->validate(['is_contacted' => ['required', 'boolean']]);

        $lead->update([
            'is_contacted' => (bool) $data['is_contacted'],
            'needs_followup' => ! $data['is_contacted'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok', 'is_contacted' => $lead->is_contacted]);
        }

        return back()->with('success', 'وضعیت لید به‌روزرسانی شد.');
    }

    public function updateNote(Request $request, Lead $lead)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:5000']]);

        $lead->update($data);

        return back()->with('success', 'یادداشت ذخیره شد.');
    }

    /** داده کشوی جزئیات در لیست */
    public function json(Lead $lead)
    {
        return response()->json([
            'id' => $lead->id,
            'name' => $lead->full_name,
            'mobile' => $lead->mobile,
            'email' => $lead->email,
            'company' => $lead->company,
            'request_type' => $lead->request_type,
            'message' => $lead->message,
            'page' => $lead->page,
            'referrer' => $lead->referrer,
            'ip' => $lead->ip,
            'utm' => collect([$lead->utm_source, $lead->utm_medium, $lead->utm_campaign])->filter()->implode(' / ') ?: null,
            'is_contacted' => (bool) $lead->is_contacted,
            'admin_note' => $lead->admin_note,
            'date' => $lead->created_at ? jdate($lead->created_at)->format('Y/m/d H:i') : null,
            'urls' => [
                'show' => route('panel.leads.show', $lead),
                'status' => route('panel.leads.status', $lead),
                'note' => route('panel.leads.note', $lead),
            ],
        ]);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('panel.leads.index')->with('success', 'لید حذف شد.');
    }
}
