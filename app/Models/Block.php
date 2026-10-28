<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** یک بلوک از صفحه‌ی صفحه‌ساز؛ اسکیما و محتوای پیش‌فرض نوعش در config/blocks.php زیر `type`. */
class Block extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['data' => 'array', 'is_active' => 'boolean'];
    }

    protected function asJson($value, $flags = 0)
    {
        return json_encode($value, $flags | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected static function booted(): void
    {
        static::saved(fn (Block $b) => $b->page?->touch());
        static::deleted(fn (Block $b) => $b->page?->touch());
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** آیا نوع این بلوک هنوز در کتابخانه هست؟ */
    public function isKnown(): bool
    {
        return config()->has('blocks.'.$this->type);
    }

    public function definition(): array
    {
        return (array) config('blocks.'.$this->type, []);
    }

    public function label(): string
    {
        return $this->definition()['label'] ?? $this->type;
    }

    public function icon(): string
    {
        return $this->definition()['icon'] ?? 'ph-warning';
    }

    /** محتوای ذخیره‌شده روی پیش‌فرض‌های کتابخانه. */
    public function content(): array
    {
        return array_replace((array) ($this->definition()['defaults'] ?? []), (array) ($this->data ?? []));
    }

    /** شناسه‌ی HTML بلوک در صفحه (برای لینک #anchor و منو). */
    public function anchorId(): string
    {
        return $this->anchor ?: $this->type.'-'.$this->id;
    }

    /** خلاصه‌ی یک‌خطی برای فهرست بلوک‌ها در پنل. */
    public function summary(): string
    {
        $c = $this->content();
        foreach (['title', 'heading', 'name'] as $k) {
            if (! empty($c[$k]) && is_string($c[$k])) {
                return \Illuminate\Support\Str::limit(trim(strip_tags($c[$k])), 60);
            }
        }

        return '';
    }
}
