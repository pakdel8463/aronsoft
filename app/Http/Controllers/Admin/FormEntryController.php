<?php

namespace App\Http\Controllers\Admin;

use App\Forms\FormSubmitter;
use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** ورودی‌های فرم‌ها: فهرست با فیلتر، جزئیات، وضعیت/ستاره/یادداشت، عملیات گروهی، دانلود پیوست و خروجی Excel. */
class FormEntryController extends Controller
{
    public function index(Request $request)
    {
        $form = $request->filled('form') ? Form::findOrFail($request->integer('form')) : null;
        $entries = $this->query($request, $form)->with('form')->latest()->paginate(25)->withQueryString();

        return view('admin.dash.forms.entries', [
            'form' => $form,
            'entries' => $entries,
            'forms' => Form::withCount(['entries as new_count' => fn ($q) => $q->where('status', 'new')])->orderBy('title')->get(),
            'counts' => FormEntry::when($form, fn ($q) => $q->where('form_id', $form->id))->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'starred' => FormEntry::when($form, fn ($q) => $q->where('form_id', $form->id))->where('is_starred', true)->count(),
            'columns' => $form ? array_slice(array_values(array_filter($form->inputFields(), fn ($f) => ! in_array($f['type'], ['hidden', 'file', 'consent', 'textarea'], true))), 0, 3) : [],
            'status' => (string) $request->query('status', ''),
            'q' => (string) $request->query('q', ''),
        ]);
    }

    public function show(Request $request, FormEntry $entry)
    {
        if ($entry->status === 'new') {
            $entry->update(['status' => 'in_progress', 'handled_by' => $request->user()->id]);
        }
        $entry->load('form', 'user', 'handler');

        return view('admin.dash.forms.entry', [
            'entry' => $entry,
            'prev' => FormEntry::where('form_id', $entry->form_id)->where('id', '<', $entry->id)->max('id'),
            'next' => FormEntry::where('form_id', $entry->form_id)->where('id', '>', $entry->id)->min('id'),
        ]);
    }

    public function update(Request $request, FormEntry $entry)
    {
        $data = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(FormEntry::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:5000'],
            'is_starred' => ['nullable', 'boolean'],
        ]);
        $update = array_filter($data, fn ($v) => $v !== null) + ['handled_by' => $request->user()->id];
        if ($request->has('admin_note')) {
            $update['admin_note'] = $data['admin_note'] ?? null;
        }
        $entry->update($update);

        return $request->expectsJson() ? response()->json(['ok' => true, 'message' => 'ذخیره شد.']) : back()->with('success', 'ورودی به‌روزرسانی شد.');
    }

    public function bulk(Request $request, FormSubmitter $files)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'], 'ids.*' => ['integer'],
            'action' => ['required', Rule::in(array_merge(array_keys(FormEntry::STATUSES), ['delete', 'star', 'unstar']))],
        ], ['ids.required' => 'هیچ ورودی‌ای انتخاب نشده است.']);
        $query = FormEntry::whereIn('id', $data['ids']);
        match ($data['action']) {
            'delete' => $query->get()->each(function ($e) use ($files) { $files->deleteFiles($e); $e->delete(); }),
            'star' => $query->update(['is_starred' => true]),
            'unstar' => $query->update(['is_starred' => false]),
            default => $query->update(['status' => $data['action'], 'handled_by' => $request->user()->id]),
        };

        return back()->with('success', 'روی '.\App\Helpers\Fa::toFa(count($data['ids'])).' ورودی انجام شد.');
    }

    public function destroy(FormEntry $entry, FormSubmitter $files)
    {
        $form = $entry->form_id;
        $files->deleteFiles($entry);
        $entry->delete();

        return redirect()->route('panel.form-entries.index', ['form' => $form])->with('success', 'ورودی حذف شد.');
    }

    public function file(FormEntry $entry, string $key, FormSubmitter $files)
    {
        return $files->download($entry, $key);
    }

    /** CSV با BOM (UTF-8) که مستقیم در Excel باز می‌شود. */
    public function export(Request $request): StreamedResponse
    {
        $form = Form::findOrFail($request->integer('form'));
        $fields = $form->inputFields();
        $query = $this->query($request, $form)->oldest();

        return response()->streamDownload(function () use ($query, $fields) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_merge(['شناسه', 'تاریخ ثبت'], array_column($fields, 'label'), ['وضعیت', 'یادداشت', 'آدرس صفحه', 'IP']));
            $query->chunk(500, function ($rows) use ($out, $fields) {
                foreach ($rows as $e) {
                    fputcsv($out, array_merge(
                        [$e->id, jdate($e->created_at)->format('Y/m/d H:i')],
                        array_map(fn ($f) => $e->display($f), $fields),
                        [FormEntry::STATUSES[$e->status] ?? $e->status, $e->admin_note, $e->source_url, $e->ip],
                    ));
                }
            });
            fclose($out);
        }, 'entries-'.$form->slug.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function query(Request $request, ?Form $form)
    {
        return FormEntry::query()
            ->when($form, fn ($q) => $q->where('form_id', $form->id))
            ->when($request->input('status'), fn ($q, $s) => $s === 'starred' ? $q->where('is_starred', true) : $q->where('status', $s), fn ($q) => $q->where('status', '!=', 'spam'))
            ->when($request->input('q'), fn ($q, $s) => $q->where('data', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $s).'%'));
    }
}
