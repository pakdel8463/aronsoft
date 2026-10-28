<?php

namespace App\Forms;

use App\Helpers\Notifier;
use App\Mail\FormConfirmationMail;
use App\Models\Form;
use App\Models\FormEntry;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/** اعتبارسنجی و ذخیره‌ی یک ارسال فرم، و بعد اعلان‌ها. */
class FormSubmitter
{
    /** فایل‌های آپلودی خصوصی‌اند (storage/app/private) و فقط از پنل دانلود می‌شوند. */
    public const DISK = 'local';

    /** @throws \Illuminate\Validation\ValidationException */
    public function handle(Form $form, Request $request): FormEntry
    {
        $fields = $form->fields ?? [];
        $raw = (array) $request->input('fields', []);

        $values = [];
        foreach ($fields as $f) {
            if (FormSchema::isInput($f['type']) && $f['type'] !== 'file') {
                $values[$f['key']] = FormSchema::cleanValue($f, $raw[$f['key']] ?? null);
            }
        }
        $visible = FormSchema::visibleKeys($fields, $values);

        [$rules, $messages, $attributes] = FormSchema::rules($fields, $visible);
        $payload = ['fields' => $values + array_filter((array) $request->file('fields', []))];
        if ($form->setting('captcha') && \App\Captcha\Captcha::enabled()) {
            $rules['captcha'] = ['required', new \App\Captcha\CaptchaRule];
            $payload['captcha'] = $request->input('captcha');
            $attributes['captcha'] = 'کد امنیتی';
        }
        Validator::make($payload, $rules, $messages, $attributes)->validateWithBag('form_'.$form->id);

        $data = [];
        foreach ($fields as $f) {
            if (! FormSchema::isInput($f['type']) || ! in_array($f['key'], $visible, true)) {
                continue;
            }
            if ($f['type'] === 'file') {
                $file = $request->file('fields.'.$f['key']);
                $data[$f['key']] = $file instanceof UploadedFile ? $this->storeFile($form, $file) : null;

                continue;
            }
            $data[$f['key']] = $values[$f['key']];
        }

        $entry = $form->entries()->create([
            'data' => $data,
            'user_id' => $request->user()?->id,
            'status' => 'new',
            'source_url' => mb_substr((string) ($request->input('_source') ?: url()->previous()), 0, 500),
            'ip' => $form->setting('store_ip') ? $request->ip() : null,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 250),
        ]);

        $this->notify($form, $entry);

        return $entry;
    }

    private function storeFile(Form $form, UploadedFile $file): array
    {
        $ext = Str::lower($file->getClientOriginalExtension() ?: (string) $file->extension());
        $path = $file->storeAs('forms/'.$form->id.'/'.now()->format('Y-m'), Str::random(32).'.'.$ext, self::DISK);

        return ['path' => $path, 'name' => mb_substr($file->getClientOriginalName(), 0, 150), 'size' => $file->getSize()];
    }

    private function notify(Form $form, FormEntry $entry): void
    {
        $s = $form->allSettings();

        if ($s['notify_admin']) {
            $rows = [];
            foreach ($form->inputFields() as $f) {
                $v = $entry->display($f);
                if ($v !== '' && $f['type'] !== 'hidden') {
                    $rows[$f['label']] = mb_substr($v, 0, 500);
                }
            }
            // اعلان پنل/پیامک/ایمیل طبق «تنظیمات اعلان‌ها» در پنل
            Notifier::adminAlert(FormSchema::mergeTags($s['notify_subject'], $form, $entry), 'ورودی جدید فرم «'.$form->title.'»', $rows, route('panel.form-entries.show', $entry));

            // آدرس‌های اضافه‌ی همین فرم
            foreach (array_filter(array_map('trim', preg_split('/[\s,;]+/', (string) $s['notify_to']) ?: [])) as $address) {
                if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
                    Notifier::to($address, new FormConfirmationMail(
                        FormSchema::mergeTags($s['notify_subject'], $form, $entry),
                        FormSchema::mergeTags('{all_fields}', $form, $entry),
                        route('panel.form-entries.show', $entry),
                    ));
                }
            }
        }

        if ($s['confirm_user'] && $s['confirm_to'] && is_string($email = $entry->data[$s['confirm_to']] ?? null) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Notifier::to($email, new FormConfirmationMail(
                FormSchema::mergeTags($s['confirm_subject'], $form, $entry),
                FormSchema::mergeTags($s['confirm_body'], $form, $entry),
            ));
        }
    }

    public function download(FormEntry $entry, string $key)
    {
        $file = $entry->data[$key] ?? null;
        abort_unless(is_array($file) && isset($file['path']) && Storage::disk(self::DISK)->exists($file['path']), 404);

        return Storage::disk(self::DISK)->download($file['path'], $file['name'] ?? basename($file['path']));
    }

    public function deleteFiles(FormEntry $entry): void
    {
        foreach ($entry->data ?? [] as $value) {
            if (is_array($value) && isset($value['path'])) {
                Storage::disk(self::DISK)->delete($value['path']);
            }
        }
    }
}
