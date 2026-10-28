<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * صفحه‌ی صفحه‌ساز. آدرس عمومی: /{slug} (از طریق Route::fallback، پس مسیرهای موجود سایت همیشه اولویت دارند).
 * is_home: به‌جای صفحه‌ی اصلی ثابت سایت نمایش داده می‌شود. is_template: قالب؛ آدرس عمومی ندارد.
 */
class Page extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_home' => 'boolean',
            'is_template' => 'boolean',
            'settings' => 'array',
        ];
    }

    protected function asJson($value, $flags = 0)
    {
        return json_encode($value, $flags | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('sort')->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePages(Builder $q): Builder
    {
        return $q->where('is_template', false);
    }

    public function scopeTemplates(Builder $q): Builder
    {
        return $q->where('is_template', true);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_template', false)->where('is_active', true);
    }

    /** صفحه‌ی اصلی ساخته‌شده با صفحه‌ساز (اگر فعال شده باشد). */
    public static function home(): ?self
    {
        return static::published()->where('is_home', true)->first();
    }

    public function url(): string
    {
        return $this->is_home ? route('home') : url($this->slug);
    }

    /** آدرس پیش‌نمایش در پنل (برای قالب‌ها و پیش‌نویس‌ها هم کار می‌کند). */
    public function previewUrl(): string
    {
        return route('panel.pages.preview', $this);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        $v = ($this->settings ?? [])[$key] ?? null;

        return ($v === null || $v === '') ? $default : $v;
    }

    /** بلوک‌های فعالی که نوعشان هنوز در کتابخانه وجود دارد. */
    public function renderableBlocks(): Collection
    {
        return $this->blocks->filter(fn (Block $b) => $b->is_active && $b->isKnown())->values();
    }
}
