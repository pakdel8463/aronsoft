<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    use HasSlug;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'status',
        'author_id',
        'featured_image',
        'alt',
        'meta_title',
        'meta_description',
        'seo_score',
        'published_at',
        'commentable',
        'password',
        'excerpt',
        'canonical_url',
        'og_image',
        'noindex',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'commentable' => 'boolean',
        'noindex' => 'boolean',
    ];

    protected $hidden = ['password'];

    /** اسلاگ یکتا و فارسی‌ایمن (رکوردهای حذف‌شده هم در نظر گرفته می‌شوند) */
    public static function generateUniqueSlug($value, $ignoreId = null, $column = 'slug')
    {
        return \App\Support\Slugger::unique(static::class, (string) $value, $ignoreId ? (int) $ignoreId : null, $column, 'post');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function revisions()
    {
        return $this->hasMany(PostRevision::class)->latest('created_at')->latest('id');
    }

    /** منتشرشده و زمان انتشار رسیده */
    public function isLive(): bool
    {
        return $this->status === 'published' && (! $this->published_at || $this->published_at->lte(now()));
    }

    /** زمان‌بندی‌شده = منتشرشده با تاریخ آینده */
    public function isScheduled(): bool
    {
        return $this->status === 'published' && $this->published_at && $this->published_at->gt(now());
    }

    /** وضعیت نمایشی: draft|published|scheduled|private */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->isScheduled()) {
            return 'scheduled';
        }

        return $this->status;
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '>', now());
    }

    public function getFeaturedUrlAttribute(): ?string
    {
        $p = $this->featured_image;
        if (! $p) {
            return null;
        }

        return preg_match('#^(https?:)?//#', $p) ? $p : asset(ltrim($p, '/'));
    }

    /** آدرس عمومی نوشته */
    public function publicUrl(): string
    {
        return route('blog.show', ['post' => $this->slug]);
    }

    public function author() {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories() {
        return $this->belongsToMany(Category::class, 'category_post');
    }

    public function tags() {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    /** فقط پست‌های منتشرشده (و زمان انتشار رسیده) */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * فقط کامنت‌های تایید شده (فرانت)
     */
    public function approvedComments()
    {
        return $this->hasMany(Comment::class, 'post_id')
            ->where('is_approved', true);
    }

    /**
     * فقط کامنت‌های سطح اول (بدون parent)
     */
    public function rootComments()
    {
        return $this->hasMany(Comment::class, 'post_id')
            ->whereNull('parent_id');
    }

    /**
     * کامنت‌های قابل نمایش (پیشنهادی برای فرانت)
     */
    public function publicComments()
    {
        return $this->hasMany(Comment::class, 'post_id')
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->with(['replies' => function ($q) {
                $q->where('is_approved', true);
            }]);
    }
}

