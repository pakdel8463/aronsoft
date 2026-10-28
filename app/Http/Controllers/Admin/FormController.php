<?php

namespace App\Http\Controllers\Admin;

use App\Forms\FormSchema;
use App\Forms\FormSubmitter;
use App\Forms\FormTemplates;
use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** فرم‌ساز: فهرست، ساخت از قالب، ویرایش فیلدها و تنظیمات، کپی، پیش‌نمایش و حذف. */
class FormController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $forms = Form::withCount(['entries', 'entries as new_entries_count' => fn ($w) => $w->where('status', 'new')])
            ->when($q !== '', fn ($w) => $w->where('title', 'like', "%{$q}%"))
            ->latest('updated_at')->get();

        return view('admin.dash.forms.index', ['forms' => $forms, 'templates' => FormTemplates::all(), 'q' => $q]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'template' => ['nullable', Rule::in(array_keys(FormTemplates::all()))],
        ], [], ['title' => 'عنوان فرم']);
        $tpl = FormTemplates::all()[$data['template'] ?? 'blank'];
        $form = Form::create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'fields' => FormSchema::normalizeFields($tpl['fields']),
            'settings' => FormSchema::normalizeSettings($tpl['settings']),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('panel.forms.edit', $form)->with('success', 'فرم ساخته شد؛ فیلدها را تنظیم کنید.');
    }

    public function edit(Form $form)
    {
        return view('admin.dash.forms.edit', [
            'form' => $form,
            'settings' => $form->allSettings(),
            'types' => FormSchema::TYPES,
            'groups' => FormSchema::GROUPS,
            'operators' => FormSchema::OPERATORS,
        ]);
    }

    public function update(Request $request, Form $form)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'fields_json' => ['required', 'json'],
            'settings' => ['array'],
            'settings.redirect_url' => ['nullable', 'string', 'max:500', \App\Support\ProductPageSchema::urlRule()],
        ], [], ['title' => 'عنوان فرم', 'settings.redirect_url' => 'آدرس مقصد']);

        $slug = Str::slug(($data['slug'] ?? '') ?: $data['title']) ?: 'form-'.$form->id;
        if (Form::where('slug', $slug)->whereKeyNot($form->id)->exists()) {
            $slug .= '-'.$form->id;
        }
        $form->update([
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'fields' => FormSchema::normalizeFields(json_decode($data['fields_json'], true) ?: []),
            'settings' => FormSchema::normalizeSettings((array) $request->input('settings', [])), // (validated() keeps only settings.redirect_url)
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->to(route('panel.forms.edit', $form).'#'.preg_replace('/[^a-z]/', '', (string) $request->input('_tab', 'fields')))
            ->with('success', 'فرم ذخیره شد.');
    }

    public function duplicate(Request $request, Form $form)
    {
        $copy = $form->replicate(['slug']);
        $copy->title = $form->title.' (کپی)';
        $copy->slug = $this->uniqueSlug($copy->title);
        $copy->updated_by = $request->user()->id;
        $copy->save();

        return redirect()->route('panel.forms.edit', $copy)->with('success', 'از فرم یک کپی ساخته شد.');
    }

    public function preview(Form $form)
    {
        return view('front.t-one.forms.preview', ['form' => $form]);
    }

    public function destroy(Form $form, FormSubmitter $files)
    {
        $form->entries()->each(fn ($e) => $files->deleteFiles($e));
        $title = $form->title;
        $form->delete();

        return redirect()->route('panel.forms.index')->with('success', "فرم «{$title}» و ورودی‌هایش حذف شد.");
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'form';
        $slug = $base;
        for ($i = 2; Form::where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
