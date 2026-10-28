<?php

namespace App\Forms;

use App\Models\Form;

/**
 * شورت‌کد فرم داخل متن‌های ویرایشگر:  [form id="3"]   [form slug="contact"]   [form id="3" title="عنوان"]
 */
final class Shortcodes
{
    private const PATTERN = '/(?:<p>\s*)?\[form\s+([^\]]*)\](?:\s*<\/p>)?/u';

    public static function render(?string $html): string
    {
        $html = (string) $html;
        if (! str_contains($html, '[form')) {
            return $html;
        }

        return preg_replace_callback(self::PATTERN, function ($m) {
            $attrs = self::attributes(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $form = isset($attrs['id']) ? Form::find((int) $attrs['id']) : (isset($attrs['slug']) ? Form::where('slug', $attrs['slug'])->first() : null);
            if (! $form) {
                return auth()->user()?->is_staff || auth()->user()?->is_superuser ? '<p style="color:#b91c1c">[فرم پیدا نشد]</p>' : '';
            }

            return '<div class="not-rich my-8">'.view('front.t-one.forms.form', ['form' => $form, 'title' => $attrs['title'] ?? null])->render().'</div>';
        }, $html) ?? $html;
    }

    private static function attributes(string $text): array
    {
        preg_match_all('/(\w+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|(\S+))/u', $text, $m, PREG_SET_ORDER);
        $out = [];
        foreach ($m as $a) {
            $out[strtolower($a[1])] = collect([$a[2] ?? '', $a[3] ?? '', $a[4] ?? ''])->first(fn ($v) => $v !== '') ?? '';
        }

        return $out;
    }
}
