<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * technologies: در دیتابیس به‌صورت JSON (آرایه) ذخیره می‌شود،
 * اما مقادیر قدیمی «A, B, C» یا «A|B» را هم به آرایه تبدیل می‌کند.
 */
class TechList implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map(fn ($v) => trim((string) $v), $decoded), 'strlen'));
        }
        $raw = is_string($decoded) ? $decoded : (string) $value;

        return array_values(array_filter(array_map('trim', preg_split('/[,،|]/u', $raw)), 'strlen'));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_string($value)) {
            $value = preg_split('/[,،|]/u', $value);
        }
        $list = array_values(array_unique(array_filter(array_map(fn ($v) => trim((string) $v), (array) $value), 'strlen')));

        return $list ? json_encode($list, JSON_UNESCAPED_UNICODE) : null;
    }
}
