<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    public const FALLBACK_SLUG = 'uncategorized';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'is_active',
        'type',
        'language',
        'sort_order',
        'image',
        'meta_title',
        'meta_description',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'category_post');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function portfolios()
    {
        return $this->belongsToMany(Portfolio::class, 'category_portfolio');
    }

    /** blog (یا type خالی) | portfolio */
    public function scopeOfType(Builder $q, string $type): Builder
    {
        return $type === 'portfolio'
            ? $q->where('type', 'portfolio')
            : $q->where(fn ($w) => $w->whereNull('type')->orWhere('type', '!=', 'portfolio'));
    }

    public function getKindAttribute(): string
    {
        return $this->type === 'portfolio' ? 'portfolio' : 'blog';
    }

    /** شناسه‌ی خودش و همه‌ی نوادگانش */
    public function descendantIds(): array
    {
        $ids = [];
        $frontier = [$this->id];
        while ($frontier) {
            $next = static::whereIn('parent_id', $frontier)->pluck('id')->all();
            $next = array_values(array_diff($next, $ids, [$this->id]));
            $ids = array_merge($ids, $next);
            $frontier = $next;
        }

        return $ids;
    }

    public static function generateUniqueSlug($value, $ignoreId = null, $column = 'slug')
    {
        return \App\Support\Slugger::unique(static::class, (string) $value, $ignoreId ? (int) $ignoreId : null, $column, 'category');
    }

    /** دسته‌ی «بدون دسته‌بندی» برای نوشته‌هایی که دسته‌ی دیگری ندارند */
    public static function fallback(): self
    {
        return static::withTrashed()->firstOrCreate(
            ['slug' => self::FALLBACK_SLUG],
            ['name' => 'بدون دسته‌بندی', 'type' => 'blog', 'is_active' => true, 'language' => 'fa']
        )->tap(function ($c) {
            if ($c->trashed()) {
                $c->restore();
            }
        });
    }
}
