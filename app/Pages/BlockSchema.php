<?php

namespace App\Pages;

use App\Support\HtmlSanitizer;
use App\Support\ProductPageSchema;

/**
 * اعتبارسنجی و پاک‌سازی محتوای بلوک‌ها بر اساس اسکیمای config/blocks.php.
 * نوع‌های مشترک (text, textarea, lines, url, image, icon, select, toggle, color, repeater) همان قواعد
 * ویرایشگر صفحات محصول (ProductPageSchema) را دارند؛ این کلاس editor، number و code را اضافه می‌کند.
 */
final class BlockSchema
{
    public const EXTRA_TYPES = ['editor', 'number', 'code'];

    /** قوانین اعتبارسنجی برای ورودی data[...] یک بلوک. */
    public static function rules(array $fields, string $prefix = 'data'): array
    {
        $rules = [];
        foreach ($fields as $f) {
            $path = $prefix.'.'.$f['key'];
            switch ($f['type']) {
                case 'editor':
                    $rules[$path] = ['nullable', 'string', 'max:'.((int) ($f['max'] ?? 60000))];
                    break;
                case 'code':
                    $rules[$path] = ['nullable', 'string', 'max:'.((int) ($f['max'] ?? 20000))];
                    break;
                case 'number':
                    $rules[$path] = array_values(array_filter(['nullable', 'integer',
                        isset($f['min']) ? 'min:'.$f['min'] : null, isset($f['max']) ? 'max:'.$f['max'] : null]));
                    break;
                default:
                    // note: select option keys must be non-numeric strings (PHP casts '2' keys to int)
                    $rules += self::sharedRules($path, $f);
            }
        }

        return $rules;
    }

    public static function attributes(array $fields, string $prefix = 'data'): array
    {
        $attrs = [];
        foreach ($fields as $f) {
            $attrs[$prefix.'.'.$f['key']] = $f['label'];
            if ($f['type'] === 'repeater') {
                foreach ($f['fields'] as $sf) {
                    $attrs[$prefix.'.'.$f['key'].'.*.'.$sf['key']] = $f['label'].' ← '.$sf['label'];
                }
            }
        }

        return $attrs;
    }

    /** ورودی خام → ساختار تمیز و کامل (همه‌ی کلیدهای اسکیما حاضرند). */
    public static function sanitize(array $fields, array $input): array
    {
        $out = [];
        foreach ($fields as $f) {
            $out[$f['key']] = self::clean($input[$f['key']] ?? null, $f);
        }

        return $out;
    }

    public static function clean(mixed $v, array $f): mixed
    {
        return match ($f['type']) {
            'editor' => HtmlSanitizer::clean(is_string($v) ? $v : '', false),
            'code' => is_string($v) ? trim(str_replace("\0", '', $v)) : '',
            'number' => self::number($v, $f),
            default => ProductPageSchema::cleanValue($v, $f),
        };
    }

    private static function number(mixed $v, array $f): int
    {
        $n = is_numeric($v) ? (int) $v : (int) ($f['default'] ?? 0);
        if (isset($f['min'])) {
            $n = max((int) $f['min'], $n);
        }
        if (isset($f['max'])) {
            $n = min((int) $f['max'], $n);
        }

        return $n;
    }

    /** قوانین نوع‌های مشترک (همان منطق ProductPageSchema::fieldRules که private است). */
    private static function sharedRules(string $path, array $f): array
    {
        $max = (int) ($f['max'] ?? 0);
        $rules = [];
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
                $rules[$path] = ['nullable', 'string', 'max:500', ProductPageSchema::urlRule()];
                break;
            case 'image':
                $rules[$path] = ['nullable', 'string', 'max:500', ProductPageSchema::imageRule()];
                break;
            case 'icon':
                $rules[$path] = ['nullable', 'string', 'in:'.implode(',', ProductPageSchema::icons())];
                break;
            case 'select':
                $rules[$path] = ['nullable', 'string', 'in:'.implode(',', array_map('strval', array_keys($f['options'] ?? [])))];
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
                    $rules += self::sharedRules($path.'.*.'.$sf['key'], $sf);
                }
                break;
        }

        return $rules;
    }
}
