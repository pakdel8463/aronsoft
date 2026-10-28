<?php

namespace App\Models;

use App\Forms\FormSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** فرم فرم‌ساز. فیلدها و تنظیمات JSON هستند؛ ساختارشان در App\Forms\FormSchema. */
class Form extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['fields' => 'array', 'settings' => 'array', 'is_active' => 'boolean'];
    }

    protected function asJson($value, $flags = 0)
    {
        return json_encode($value, $flags | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FormEntry::class);
    }

    /** تنظیمات ذخیره‌شده روی پیش‌فرض‌ها. */
    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->allSettings()[$key] ?? $default;
    }

    public function allSettings(): array
    {
        return array_replace(FormSchema::defaultSettings(), $this->settings ?? []);
    }

    /** فیلدهایی که مقدار می‌گیرند (بدون عنوان بخش، HTML و شکست صفحه). */
    public function inputFields(): array
    {
        return array_values(array_filter($this->fields ?? [], fn ($f) => FormSchema::isInput($f['type'] ?? '')));
    }

    public function shortcode(): string
    {
        return '[form id="'.$this->id.'"]';
    }

    /** null یعنی فرم ورودی می‌پذیرد؛ وگرنه پیامی که به بازدیدکننده نشان داده می‌شود. */
    public function closedReason(): ?string
    {
        $closed = $this->setting('closed_message') ?: 'این فرم در حال حاضر فعال نیست.';
        if (! $this->is_active) {
            return $closed;
        }
        $now = now();
        if (($open = $this->setting('open_at')) && $now->lt($open)) {
            return $closed;
        }
        if (($close = $this->setting('close_at')) && $now->gt($close)) {
            return $closed;
        }
        if (($limit = (int) $this->setting('limit_entries')) > 0 && $this->entries()->where('status', '!=', 'spam')->count() >= $limit) {
            return $this->setting('limit_message') ?: 'ظرفیت این فرم تکمیل شده است.';
        }

        return null;
    }
}
