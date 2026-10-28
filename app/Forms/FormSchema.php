<?php

namespace App\Forms;

use App\Helpers\Fa;
use App\Support\HtmlSanitizer;
use App\Support\NationalCode;
use Illuminate\Support\Str;

/**
 * Field catalogue, normalisation, conditional logic and validation rules for
 * the form builder (ported from the setareh-mandegar panel). The same logic
 * semantics are mirrored in public/js/forms.js.
 *
 * A field looks like:
 * [
 *   'id' => 'f8k2j1', 'type' => 'text', 'key' => 'name', 'label' => 'نام',
 *   'placeholder' => '', 'help' => '', 'required' => true, 'default' => '',
 *   'width' => 'full|half|third', 'min' => null, 'max' => null, 'rows' => 4,
 *   'options' => [['label' => 'الف', 'value' => 'الف']],
 *   'accept' => 'jpg,png,pdf', 'max_kb' => 5120, 'content' => '<p>…</p>',
 *   'logic' => ['enabled' => true, 'action' => 'show|hide', 'match' => 'all|any',
 *               'rules' => [['field' => 'other_key', 'op' => 'is', 'value' => 'x']]],
 * ]
 */
final class FormSchema
{
    /** type => [label, icon, group, is_input] */
    public const TYPES = [
        'text' => ['متن کوتاه', 'text-t', 'basic', true],
        'textarea' => ['متن بلند', 'text-align-right', 'basic', true],
        'email' => ['ایمیل', 'envelope-simple', 'basic', true],
        'mobile' => ['شماره موبایل', 'device-mobile', 'basic', true],
        'tel' => ['شماره تلفن', 'phone', 'basic', true],
        'number' => ['عدد', 'hash', 'basic', true],
        'national_code' => ['کد ملی', 'identification-card', 'basic', true],
        'select' => ['لیست کشویی', 'caret-circle-down', 'choice', true],
        'radio' => ['انتخاب یکی (رادیو)', 'radio-button', 'choice', true],
        'checkbox' => ['انتخاب چندتایی', 'check-square', 'choice', true],
        'consent' => ['تأیید / پذیرش قوانین', 'shield-check', 'choice', true],
        'date' => ['تاریخ شمسی', 'calendar-blank', 'advanced', true],
        'file' => ['آپلود فایل', 'upload-simple', 'advanced', true],
        'url' => ['آدرس وب‌سایت', 'link', 'advanced', true],
        'hidden' => ['فیلد مخفی', 'eye-slash', 'advanced', true],
        'section' => ['عنوان بخش', 'text-h', 'layout', false],
        'html' => ['متن / HTML', 'code', 'layout', false],
        'page' => ['شکست صفحه (مرحله بعد)', 'stack', 'layout', false],
    ];

    public const GROUPS = ['basic' => 'فیلدهای پایه', 'choice' => 'گزینه‌ای', 'advanced' => 'پیشرفته', 'layout' => 'چیدمان و چندمرحله‌ای'];

    public const OPERATORS = [
        'is' => 'برابر است با', 'isnot' => 'برابر نیست با', 'contains' => 'شامل', 'notcontains' => 'شامل نیست',
        'gt' => 'بزرگ‌تر از', 'lt' => 'کوچک‌تر از', 'empty' => 'خالی است', 'notempty' => 'خالی نیست',
    ];

    public static function isInput(string $type): bool
    {
        return self::TYPES[$type][3] ?? false;
    }

    public static function hasOptions(string $type): bool
    {
        return in_array($type, ['select', 'radio', 'checkbox'], true);
    }

    public static function defaultSettings(): array
    {
        return [
            'submit_label' => 'ارسال',
            'next_label' => 'مرحله بعد',
            'prev_label' => 'مرحله قبل',
            'progress' => 'steps',          // steps | bar | none (multi-step forms)
            'label_placement' => 'top',     // top | hidden (placeholders only)
            'confirmation_type' => 'message', // message | redirect
            'confirmation_message' => '<p>با تشکر؛ اطلاعات شما با موفقیت ثبت شد.</p>',
            'redirect_url' => '',
            'hide_after_submit' => true,
            'notify_admin' => true,
            'notify_to' => '',              // extra addresses; the panel's notification settings always apply
            'notify_subject' => 'ورودی جدید فرم «{form_title}»',
            'notify_reply_to' => '',        // key of an email field
            'confirm_user' => false,
            'confirm_to' => '',             // key of an email field
            'confirm_subject' => 'دریافت اطلاعات شما — {form_title}',
            'confirm_body' => "سلام،\nاطلاعات شما دریافت شد و به‌زودی بررسی می‌شود.\n\n{all_fields}",
            'require_login' => false,
            'limit_entries' => 0,
            'limit_message' => 'ظرفیت این فرم تکمیل شده است.',
            'open_at' => '',
            'close_at' => '',
            'closed_message' => 'این فرم در حال حاضر فعال نیست.',
            'captcha' => true,              // کپچای نامرئی proof-of-work (App\Captcha)
            'store_ip' => true,
            'css_class' => '',
        ];
    }

    /** Sanitise the field list coming from the admin builder. */
    public static function normalizeFields(array $fields): array
    {
        $out = [];
        $keys = [];
        $ids = [];
        foreach (array_values($fields) as $i => $f) {
            if (! is_array($f) || ! isset(self::TYPES[$f['type'] ?? ''])) {
                continue;
            }
            $type = $f['type'];
            $id = preg_match('/^[a-z0-9_]{3,20}$/', (string) ($f['id'] ?? '')) && ! isset($ids[$f['id']]) ? $f['id'] : 'f'.Str::lower(Str::random(6));
            $ids[$id] = true;

            $key = Str::slug((string) ($f['key'] ?? ''), '_');
            $key = preg_replace('/[^a-z0-9_]/', '', Str::lower($key));
            if ($key === '' || is_numeric($key[0] ?? '')) {
                $key = $type.'_'.($i + 1);
            }
            while (isset($keys[$key])) {
                $key .= '_'.($i + 1);
            }
            $keys[$key] = true;

            $field = [
                'id' => $id,
                'type' => $type,
                'key' => $key,
                'label' => trim(strip_tags((string) ($f['label'] ?? ''))) ?: self::TYPES[$type][0],
                'placeholder' => trim(strip_tags((string) ($f['placeholder'] ?? ''))),
                'help' => trim(strip_tags((string) ($f['help'] ?? ''))),
                'required' => self::isInput($type) && $type !== 'hidden' && filter_var($f['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'default' => is_scalar($f['default'] ?? null) ? trim((string) $f['default']) : '',
                'width' => in_array($f['width'] ?? 'full', ['full', 'half', 'third'], true) ? ($f['width'] ?? 'full') : 'full',
                'min' => self::nullableInt($f['min'] ?? null),
                'max' => self::nullableInt($f['max'] ?? null),
                'css_class' => preg_replace('/[^\w\s-]/', '', (string) ($f['css_class'] ?? '')),
            ];
            if ($type === 'textarea') {
                $field['rows'] = max(2, min(20, (int) ($f['rows'] ?? 4)));
            }
            if (self::hasOptions($type)) {
                $field['options'] = collect($f['options'] ?? [])
                    ->filter(fn ($o) => is_array($o) && trim((string) ($o['label'] ?? '')) !== '')
                    ->map(fn ($o) => [
                        'label' => trim(strip_tags((string) $o['label'])),
                        'value' => trim(strip_tags((string) (($o['value'] ?? '') !== '' ? $o['value'] : $o['label']))),
                    ])->unique('value')->values()->all();
                if ($type === 'select') {
                    $field['placeholder'] = $field['placeholder'] ?: 'انتخاب کنید';
                }
            }
            if ($type === 'file') {
                $field['accept'] = collect(explode(',', Str::lower((string) ($f['accept'] ?? 'jpg,jpeg,png,pdf'))))
                    ->map(fn ($e) => preg_replace('/[^a-z0-9]/', '', $e))->filter()
                    ->reject(fn ($e) => in_array($e, ['php', 'phtml', 'phar', 'exe', 'sh', 'js', 'html', 'htm', 'svg'], true))
                    ->unique()->implode(',') ?: 'jpg,jpeg,png,pdf';
                $field['max_kb'] = max(100, min((int) config('forms.max_upload_kb', 10240), (int) ($f['max_kb'] ?? 5120)));
            }
            if ($type === 'consent') {
                $field['content'] = HtmlSanitizer::clean((string) ($f['content'] ?? ''), false) ?: 'قوانین و مقررات را مطالعه کرده‌ام و می‌پذیرم.';
            }
            if ($type === 'html' || $type === 'section') {
                $field['content'] = HtmlSanitizer::clean((string) ($f['content'] ?? ''), false);
            }
            if ($type === 'date') {
                $field['min'] = $field['min'] ?? 1300;
                $field['max'] = $field['max'] ?? ((int) jdate(now())->format('Y') + 5);
            }
            if ($type === 'page') {
                $field['label'] = trim(strip_tags((string) ($f['label'] ?? ''))) ?: 'مرحله بعد';
            }
            $field['logic'] = self::normalizeLogic($f['logic'] ?? null);
            $out[] = $field;
        }

        // Logic may only point to input fields that exist (and not to itself).
        $valid = collect($out)->filter(fn ($f) => self::isInput($f['type']))->pluck('key')->all();
        foreach ($out as &$field) {
            $field['logic']['rules'] = array_values(array_filter($field['logic']['rules'],
                fn ($r) => in_array($r['field'], $valid, true) && $r['field'] !== $field['key']));
            if (! $field['logic']['rules']) {
                $field['logic']['enabled'] = false;
            }
        }

        return $out;
    }

    private static function normalizeLogic(mixed $logic): array
    {
        $logic = is_array($logic) ? $logic : [];

        return [
            'enabled' => filter_var($logic['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'action' => ($logic['action'] ?? 'show') === 'hide' ? 'hide' : 'show',
            'match' => ($logic['match'] ?? 'all') === 'any' ? 'any' : 'all',
            'rules' => collect($logic['rules'] ?? [])->filter(fn ($r) => is_array($r) && isset(self::OPERATORS[$r['op'] ?? '']))
                ->map(fn ($r) => ['field' => (string) ($r['field'] ?? ''), 'op' => $r['op'], 'value' => trim((string) ($r['value'] ?? ''))])
                ->values()->all(),
        ];
    }

    public static function normalizeSettings(array $input): array
    {
        $d = self::defaultSettings();
        $out = [];
        foreach ($d as $k => $default) {
            $v = $input[$k] ?? $default;
            $out[$k] = match (true) {
                is_bool($default) => filter_var($v, FILTER_VALIDATE_BOOLEAN),
                is_int($default) => max(0, (int) self::en((string) $v)),
                in_array($k, ['confirmation_message'], true) => HtmlSanitizer::clean((string) $v, false),
                in_array($k, ['confirm_body'], true) => trim(strip_tags((string) $v)),
                default => trim(strip_tags((string) $v)),
            };
        }
        $out['progress'] = in_array($out['progress'], ['steps', 'bar', 'none'], true) ? $out['progress'] : 'steps';
        $out['label_placement'] = in_array($out['label_placement'], ['top', 'hidden'], true) ? $out['label_placement'] : 'top';
        $out['confirmation_type'] = $out['confirmation_type'] === 'redirect' ? 'redirect' : 'message';
        foreach (['open_at', 'close_at'] as $k) {
            $out[$k] = $out[$k] && strtotime($out[$k]) ? date('Y-m-d H:i', strtotime($out[$k])) : '';
        }

        return $out;
    }

    /** Split fields into steps at "page" fields. Returns [[fields…], …] (at least one step). */
    public static function steps(array $fields): array
    {
        $steps = [[]];
        foreach ($fields as $f) {
            if ($f['type'] === 'page') {
                $steps[] = [];

                continue;
            }
            $steps[array_key_last($steps)][] = $f;
        }

        return array_values(array_filter($steps, fn ($s) => $s !== [])) ?: [[]];
    }

    /** Raw value as submitted, normalised for comparisons/storage. */
    public static function cleanValue(array $field, mixed $value): mixed
    {
        return match ($field['type']) {
            'checkbox' => collect(is_array($value) ? $value : [])->map(fn ($v) => trim((string) $v))->filter(fn ($v) => $v !== '')->values()->all(),
            'consent' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '',
            'mobile', 'tel', 'national_code' => preg_replace('/[\s\-]/', '', self::en(trim((string) $value))),
            'number' => self::en(trim((string) $value)),
            'date' => self::cleanDate($value),
            'file' => null,
            default => is_scalar($value) ? trim((string) $value) : '',
        };
    }

    private static function cleanDate(mixed $value): string
    {
        if (is_array($value)) {
            [$y, $m, $d] = [(int) ($value['y'] ?? 0), (int) ($value['m'] ?? 0), (int) ($value['d'] ?? 0)];

            return $y && $m && $d ? sprintf('%04d/%02d/%02d', $y, $m, $d) : '';
        }

        return preg_match('#^\d{4}/\d{1,2}/\d{1,2}$#', self::en((string) $value)) ? self::en((string) $value) : '';
    }

    /**
     * Keys of fields visible for the given values. Fields hidden by logic, and
     * fields whose rules reference hidden fields, count as empty.
     */
    public static function visibleKeys(array $fields, array $values): array
    {
        $byKey = collect($fields)->keyBy('key');
        $visible = [];
        $resolving = [];
        $isVisible = function (string $key) use (&$isVisible, &$visible, &$resolving, $byKey, $values): bool {
            if (array_key_exists($key, $visible)) {
                return $visible[$key];
            }
            $field = $byKey[$key] ?? null;
            if (! $field || empty($field['logic']['enabled']) || isset($resolving[$key])) {
                return $visible[$key] = (bool) $field;
            }
            $resolving[$key] = true;
            $results = array_map(function ($rule) use ($isVisible, $values) {
                $value = $isVisible($rule['field']) ? ($values[$rule['field']] ?? '') : '';

                return self::matches($value, $rule['op'], $rule['value']);
            }, $field['logic']['rules']);
            unset($resolving[$key]);
            $hit = $field['logic']['match'] === 'any' ? in_array(true, $results, true) : ! in_array(false, $results, true);

            return $visible[$key] = $field['logic']['action'] === 'show' ? $hit : ! $hit;
        };

        return collect($fields)->pluck('key')->filter(fn ($k) => $isVisible($k))->values()->all();
    }

    public static function matches(mixed $value, string $op, string $target): bool
    {
        $values = is_array($value) ? array_map('strval', $value) : [(string) $value];
        $joined = implode(',', $values);
        $num = fn ($v) => is_numeric(self::en((string) $v)) ? (float) self::en((string) $v) : null;

        return match ($op) {
            'is' => is_array($value) ? in_array($target, $values, true) : $joined === $target,
            'isnot' => is_array($value) ? ! in_array($target, $values, true) : $joined !== $target,
            'contains' => $target !== '' && mb_stripos($joined, $target) !== false,
            'notcontains' => $target === '' || mb_stripos($joined, $target) === false,
            'gt' => $num($joined) !== null && $num($target) !== null && $num($joined) > $num($target),
            'lt' => $num($joined) !== null && $num($target) !== null && $num($joined) < $num($target),
            'empty' => trim($joined) === '',
            'notempty' => trim($joined) !== '',
            default => false,
        };
    }

    /** Laravel validation rules for the visible input fields. */
    public static function rules(array $fields, array $visibleKeys): array
    {
        $rules = [];
        $attributes = [];
        $messages = [];
        foreach ($fields as $f) {
            if (! self::isInput($f['type']) || ! in_array($f['key'], $visibleKeys, true)) {
                continue;
            }
            $k = 'fields.'.$f['key'];
            $attributes[$k] = $f['label'];
            $attributes[$k.'.*'] = $f['label'];
            $req = $f['required'] ? 'required' : 'nullable';
            $min = $f['min'];
            $max = $f['max'];
            $r = match ($f['type']) {
                'text', 'hidden' => [$req, 'string', 'max:'.($max ?: 255)] + ($min ? [3 => 'min:'.$min] : []),
                'textarea' => [$req, 'string', 'max:'.($max ?: 10000)] + ($min ? [3 => 'min:'.$min] : []),
                'email' => [$req, 'email', 'max:190'],
                'url' => [$req, 'url', 'max:500'],
                'mobile' => [$req, 'regex:/^09\d{9}$/'],
                'tel' => [$req, 'regex:/^0\d{9,10}$/'],
                'number' => array_values(array_filter([$req, 'numeric', $min !== null ? 'min:'.$min : null, $max !== null ? 'max:'.$max : null])),
                'national_code' => [$req, function ($a, $v, $fail) {
                    if (filled($v) && ! NationalCode::valid((string) $v)) {
                        $fail('کد ملی وارد شده معتبر نیست.');
                    }
                }],
                'select', 'radio' => [$req, 'in:'.self::inList($f)],
                'checkbox' => array_values(array_filter([$f['required'] ? 'required' : 'nullable', 'array', $min ? 'min:'.$min : null, $max ? 'max:'.$max : null])),
                'consent' => [$f['required'] ? 'accepted' : 'nullable'],
                'date' => [$req, function ($a, $v, $fail) use ($f) {
                    if (filled($v) && ! self::validJalali((string) $v, (int) $f['min'], (int) $f['max'])) {
                        $fail('تاریخ وارد شده معتبر نیست.');
                    }
                }],
                'file' => [$req, 'file', 'max:'.$f['max_kb'], 'mimes:'.$f['accept']],
                default => ['nullable'],
            };
            $rules[$k] = array_values($r);
            if ($f['type'] === 'checkbox') {
                $rules[$k.'.*'] = ['in:'.self::inList($f)];
            }
            $messages[$k.'.regex'] = $f['type'] === 'mobile' ? 'شماره موبایل را به‌صورت ۱۱ رقمی (مثل ۰۹۱۲…) وارد کنید.' : 'شماره تلفن را با کد شهر وارد کنید.';
            $messages[$k.'.accepted'] = 'برای ادامه باید «'.strip_tags($f['label']).'» را تأیید کنید.';
        }

        return [$rules, $messages, $attributes];
    }

    private static function inList(array $f): string
    {
        return collect($f['options'] ?? [])->pluck('value')->map(fn ($v) => '"'.str_replace('"', '""', $v).'"')->implode(',');
    }

    private static function validJalali(string $value, int $minYear, int $maxYear): bool
    {
        if (! preg_match('#^(\d{4})/(\d{2})/(\d{2})$#', $value, $m)) {
            return false;
        }
        [$y, $mo, $d] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        $maxDay = $mo <= 6 ? 31 : ($mo <= 11 ? 30 : 30);

        return $y >= $minYear && $y <= $maxYear && $mo >= 1 && $mo <= 12 && $d >= 1 && $d <= $maxDay;
    }

    /** Value as text for emails, the admin panel and CSV export. */
    public static function display(array $field, mixed $value): string
    {
        if ($value === null || $value === '' || $value === []) {
            return '';
        }
        $label = fn ($v) => collect($field['options'] ?? [])->firstWhere('value', (string) $v)['label'] ?? (string) $v;

        return match ($field['type']) {
            'checkbox' => collect((array) $value)->map($label)->implode('، '),
            'select', 'radio' => $label($value),
            'consent' => $value ? 'تأیید شد' : '',
            'file' => is_array($value) ? (string) ($value['name'] ?? 'فایل') : '',
            default => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE),
        };
    }

    /** Replace {field:key}, {all_fields}, {form_title}, {entry_id}, {date} in a text. */
    public static function mergeTags(string $text, \App\Models\Form $form, \App\Models\FormEntry $entry, bool $html = false): string
    {
        $fields = collect($form->inputFields())->keyBy('key');
        $text = preg_replace_callback('/\{field:([a-z0-9_]+)\}/', function ($m) use ($fields, $entry, $html) {
            $v = isset($fields[$m[1]]) ? $entry->display($fields[$m[1]]) : '';

            return $html ? e($v) : $v;
        }, $text);
        $all = $fields->filter(fn ($f) => $f['type'] !== 'hidden' && filled($entry->data[$f['key']] ?? null))
            ->map(fn ($f) => $html ? '<b>'.e($f['label']).':</b> '.e($entry->display($f)) : $f['label'].': '.$entry->display($f))
            ->implode($html ? '<br>' : "\n");

        return strtr($text, [
            '{all_fields}' => $all,
            '{form_title}' => $html ? e($form->title) : $form->title,
            '{entry_id}' => (string) $entry->id,
            '{date}' => jdate($entry->created_at ?? now())->format('j F Y، H:i'),
        ]);
    }

    private static function en(string $v): string
    {
        return (string) Fa::digits($v);
    }

    private static function nullableInt(mixed $v): ?int
    {
        $v = self::en(trim((string) $v));

        return $v === '' || ! is_numeric($v) ? null : (int) $v;
    }
}
