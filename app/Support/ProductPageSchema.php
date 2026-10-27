<?php

namespace App\Support;

use Closure;

/**
 * خواندن اسکیمای config/product_pages.php + ساخت قوانین اعتبارسنجی و پاک‌سازی ورودی فرم.
 * منبع حقیقت = config؛ فرم پنل، اعتبارسنجی و سیدر همه از همین کلاس استفاده می‌کنند.
 */
class ProductPageSchema
{
    /* ------------------------------------------------------------------ قالب‌ها */

    /** همه‌ی قالب‌ها: key => [label, description, sections, palette, sample] (config/product_templates.php) */
    public static function templates(): array
    {
        return (array) config('product_templates.templates', []);
    }

    public static function defaultTemplate(): string
    {
        $d = (string) config('product_templates.default', 'ink');

        return isset(self::templates()[$d]) ? $d : (array_key_first(self::templates()) ?: 'ink');
    }

    /** کلید معتبر قالب؛ ناشناخته → قالب پیش‌فرض */
    public static function templateKey(?string $key): string
    {
        return ($key && isset(self::templates()[$key])) ? $key : self::defaultTemplate();
    }

    public static function template(?string $key = null): array
    {
        return self::templates()[self::templateKey($key)] ?? [];
    }

    /** بخش‌های قالب (اسکیما). */
    public static function sections(?string $template = null): array
    {
        return (array) (self::template($template)['sections'] ?? []);
    }

    public static function section(string $key, ?string $template = null): ?array
    {
        return self::sections($template)[$key] ?? null;
    }

    /** توکن‌های رنگ قالب: [['key','label','default'], ...] */
    public static function palette(?string $template = null): array
    {
        return (array) (self::template($template)['palette'] ?? []);
    }

    /** مقدار رنگ‌ها: ورودی ذخیره‌شده روی پیش‌فرض قالب (فقط hex معتبر) → [key => '#RRGGBB'] */
    public static function paletteValues(?string $template, ?array $stored): array
    {
        $out = [];
        foreach (self::palette($template) as $t) {
            $v = strtoupper(trim((string) (($stored ?? [])[$t['key']] ?? '')));
            $out[$t['key']] = preg_match('/^#[0-9A-F]{6}$/', $v) ? $v : strtoupper($t['default']);
        }

        return $out;
    }

    /** محتوای نمونه‌ی قالب (برای صفحه‌ی تازه یا «بازنشانی به نمونه»): sections + _palette تمیزشده */
    public static function sample(?string $template = null): array
    {
        $t = self::templateKey($template);
        $raw = (array) (self::template($t)['sample'] ?? []);

        return self::sanitize($raw, $t);
    }

    public static function icons(): array
    {
        return array_values(array_unique((array) config('product_pages.icons', [])));
    }

    public static function linkTypes(): array
    {
        return (array) config('product_pages.links', []);
    }

    /** چیدمان پیش‌فرض: [['key'=>..,'enabled'=>..], ...] (هیرو اول، CTA آخر) */
    public static function defaultLayout(?string $template = null): array
    {
        $out = [];
        foreach (self::sections($template) as $key => $def) {
            $out[] = ['key' => $key, 'enabled' => (bool) ($def['default_enabled'] ?? true)];
        }

        return self::normalizeLayout($out, $template);
    }

    /**
     * چیدمان ورودی را با اسکیما تطبیق می‌دهد: کلیدهای ناشناخته حذف، بخش‌های جاافتاده اضافه،
     * بخش‌های قفل‌شده (locked) همیشه فعال؛ هیرو اول و cta آخر.
     */
    public static function normalizeLayout(?array $layout, ?string $template = null): array
    {
        $sections = self::sections($template);
        $seen = [];
        $items = [];
        foreach ((array) $layout as $row) {
            $key = is_array($row) ? ($row['key'] ?? null) : $row;
            if (! is_string($key) || ! isset($sections[$key]) || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $items[$key] = [
                'key' => $key,
                'enabled' => ! empty($sections[$key]['locked']) ? true : (bool) (is_array($row) ? ($row['enabled'] ?? false) : true),
            ];
        }
        foreach ($sections as $key => $def) {
            if (! isset($items[$key])) {
                $items[$key] = ['key' => $key, 'enabled' => (bool) ($def['default_enabled'] ?? true) || ! empty($def['locked'])];
            }
        }

        $hero = $items['hero'] ?? null;
        $cta = $items['cta'] ?? null;
        unset($items['hero'], $items['cta']);
        $ordered = array_values($items);
        if ($hero) {
            array_unshift($ordered, $hero);
        }
        if ($cta) {
            $ordered[] = $cta;
        }

        return $ordered;
    }

    /* ------------------------------------------------------------------ URL / تصویر */

    public static function isSafeUrl(?string $v): bool
    {
        $v = trim((string) $v);
        if ($v === '') {
            return true;
        }
        if (preg_match('/[\x00-\x1F\x7F\s]/u', $v)) {
            return false;
        }

        return (bool) preg_match('#^(https?://[^\s/]+[^\s]*|/(?!/)[^\s]*|\#[A-Za-z0-9_\-]+|tel:\+?[0-9\-]+|mailto:[^\s@]+@[^\s@]+)$#i', $v);
    }

    public static function isSafeImage(?string $v): bool
    {
        $v = trim((string) $v);
        if ($v === '') {
            return true;
        }
        if (str_contains($v, '..') || preg_match('/[\x00-\x1F\x7F\s<>"\']/u', $v)) {
            return false;
        }

        return (bool) preg_match('#^(https://[^\s]+|/?[A-Za-z0-9_\-./%]+)$#', $v);
    }

    public static function imageUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        if (preg_match('#^(https?:)?//#', $path)) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    /* ------------------------------------------------------------------ اعتبارسنجی */

    public static function rules(string $prefix = 'sections', ?string $template = null): array
    {
        $rules = [];
        foreach (self::sections($template) as $secKey => $def) {
            foreach ($def['fields'] as $f) {
                self::fieldRules($rules, "{$prefix}.{$secKey}.{$f['key']}", $f);
            }
        }
        foreach (self::palette($template) as $t) {
            $rules["{$prefix}._palette.{$t['key']}"] = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        }

        return $rules;
    }

    public static function attributes(string $prefix = 'sections', ?string $template = null): array
    {
        $attrs = [];
        foreach (self::palette($template) as $t) {
            $attrs["{$prefix}._palette.{$t['key']}"] = 'رنگ ← '.$t['label'];
        }
        foreach (self::sections($template) as $secKey => $def) {
            foreach ($def['fields'] as $f) {
                $attrs["{$prefix}.{$secKey}.{$f['key']}"] = ($def['label'] ?? $secKey).' ← '.$f['label'];
                if ($f['type'] === 'repeater') {
                    foreach ($f['fields'] as $sf) {
                        $attrs["{$prefix}.{$secKey}.{$f['key']}.*.{$sf['key']}"] = ($def['label'] ?? $secKey).' ← '.$sf['label'];
                    }
                }
            }
        }

        return $attrs;
    }

    private static function fieldRules(array &$rules, string $path, array $f): void
    {
        $max = (int) ($f['max'] ?? 0);
        switch ($f['type']) {
            case 'text':
                $rules[$path] = ['nullable', 'string', 'max:'.($max ?: 255)];
                break;
            case 'textarea':
                $rules[$path] = ['nullable', 'string', 'max:'.($max ?: 1000)];
                break;
            case 'lines':
                $items = (int) ($f['max_items'] ?? 10);
                $rules[$path] = ['nullable', 'string', 'max:'.($items * (($max ?: 120) + 2))];
                break;
            case 'url':
                $rules[$path] = ['nullable', 'string', 'max:500', self::urlRule()];
                break;
            case 'image':
                $rules[$path] = ['nullable', 'string', 'max:500', self::imageRule()];
                break;
            case 'icon':
                $rules[$path] = ['nullable', 'string', 'in:'.implode(',', self::icons())];
                break;
            case 'select':
                $rules[$path] = ['nullable', 'string', 'in:'.implode(',', array_keys($f['options'] ?? []))];
                break;
            case 'toggle':
                $rules[$path] = ['nullable', 'boolean'];
                break;
            case 'color':
                $rules[$path] = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
                break;
            case 'repeater':
                $rules[$path] = ['nullable', 'array', 'max:'.(int) ($f['max_items'] ?? 12)];
                foreach ($f['fields'] as $sf) {
                    self::fieldRules($rules, "{$path}.*.{$sf['key']}", $sf);
                }
                break;
        }
    }

    public static function urlRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (! self::isSafeUrl(is_string($value) ? $value : '')) {
                $fail('آدرس واردشده معتبر نیست (با https:// ، / یا # شروع شود).');
            }
        };
    }

    public static function imageRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (! self::isSafeImage(is_string($value) ? $value : '')) {
                $fail('مسیر تصویر معتبر نیست؛ تصویر را از کتابخانه‌ی رسانه انتخاب کنید.');
            }
        };
    }

    /* ------------------------------------------------------------------ پاک‌سازی */

    /** ورودی خام فرم (sections[...]) → ساختار تمیز و کامل بر اساس اسکیما */
    public static function sanitize(?array $input, ?string $template = null): array
    {
        $input = (array) $input;
        $out = [];
        foreach (self::sections($template) as $secKey => $def) {
            $raw = (array) ($input[$secKey] ?? []);
            $sec = [];
            foreach ($def['fields'] as $f) {
                $sec[$f['key']] = self::cleanValue($raw[$f['key']] ?? null, $f);
            }
            $out[$secKey] = $sec;
        }
        // رنگ‌های قالب (فقط hex معتبر؛ خالی = پیش‌فرض قالب)
        $pal = [];
        foreach (self::palette($template) as $t) {
            $v = strtoupper(trim((string) (((array) ($input['_palette'] ?? []))[$t['key']] ?? '')));
            $pal[$t['key']] = preg_match('/^#[0-9A-F]{6}$/', $v) ? $v : '';
        }
        $out['_palette'] = $pal;

        return $out;
    }

    public static function cleanValue(mixed $v, array $f): mixed
    {
        switch ($f['type']) {
            case 'text':
                return self::str($v, (int) ($f['max'] ?? 255), false);
            case 'textarea':
                return self::str($v, (int) ($f['max'] ?? 1000), true);
            case 'lines':
                $lines = is_array($v) ? $v : preg_split('/\R/u', (string) $v);
                $lines = array_values(array_filter(array_map(fn ($l) => self::str($l, (int) ($f['max'] ?? 120), false), $lines), fn ($l) => $l !== ''));

                return array_slice($lines, 0, (int) ($f['max_items'] ?? 10));
            case 'url':
                $s = self::str($v, 500, false);

                return self::isSafeUrl($s) ? $s : '';
            case 'image':
                $s = trim((string) (is_scalar($v) ? $v : ''));

                return self::isSafeImage($s) ? mb_substr($s, 0, 500) : '';
            case 'icon':
                return in_array($v, self::icons(), true) ? $v : '';
            case 'select':
                $opts = array_keys($f['options'] ?? []);

                return in_array($v, $opts, true) ? $v : ($f['default'] ?? ($opts[0] ?? ''));
            case 'color':
                $c = strtoupper(trim((string) (is_scalar($v) ? $v : '')));

                return preg_match('/^#[0-9A-F]{6}$/', $c) ? $c : '';
            case 'toggle':
                if ($v === null) {
                    return (bool) ($f['default'] ?? false);
                }

                return filter_var($v, FILTER_VALIDATE_BOOLEAN);
            case 'repeater':
                $rows = [];
                foreach (is_array($v) ? $v : [] as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $clean = [];
                    $filled = false;
                    foreach ($f['fields'] as $sf) {
                        $val = self::cleanValue($row[$sf['key']] ?? null, $sf);
                        $clean[$sf['key']] = $val;
                        if (! in_array($sf['type'], ['toggle', 'select'], true) && $val !== '' && $val !== []) {
                            $filled = true;
                        }
                    }
                    if ($filled) {
                        $rows[] = $clean;
                    }
                }

                return array_slice($rows, 0, (int) ($f['max_items'] ?? 12));
        }

        return null;
    }

    private static function str(mixed $v, int $max, bool $multiline): string
    {
        if (! is_scalar($v)) {
            return '';
        }
        $s = (string) $v;
        // فقط کاراکترهای کنترلی حذف می‌شوند (نیم‌فاصله‌ی فارسی U+200C باید بماند)
        $s = preg_replace($multiline ? '/[^\P{Cc}\n]+/u' : '/\p{Cc}+/u', '', $s) ?? '';
        $s = trim($s);

        return $max > 0 ? mb_substr($s, 0, $max) : $s;
    }

    /** مقدار پیش‌فرض یک فیلد برای فرم ایجاد */
    public static function emptySections(?string $template = null): array
    {
        return self::sanitize([], $template);
    }
}
