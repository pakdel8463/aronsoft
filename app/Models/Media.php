<?php

namespace App\Models;

use App\Services\Media\MediaStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'file_name',
        'original_name',
        'mime_type',
        'size',
        'type',
        'width',
        'height',
        'alt',
        'title',
        'caption',
        'disk',
        'path',
        'variants',
        'extension',
        'uploaded_by',
        'hash',
    ];

    protected $casts = [
        'variants' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    // رابطه با همه مدل‌ها
    public function relations()
    {
        return $this->hasMany(MediaRelation::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /** آدرس مطلق فایل اصلی */
    public function getUrlAttribute(): string
    {
        return MediaStorage::url($this->disk ?: 'public', $this->path);
    }

    /** آدرس نسبی از ریشه‌ی سایت (برای ذخیره در پست/محتوا) */
    public function getRelUrlAttribute(): string
    {
        return MediaStorage::rel($this->disk ?: 'public', $this->path);
    }

    public function variantRel(string $key): ?string
    {
        $p = ($this->variants ?? [])[$key] ?? null;

        return $p ? MediaStorage::rel($this->disk ?: 'public', $p) : null;
    }

    public function variantUrl(string $key): ?string
    {
        $rel = $this->variantRel($key);
        if (! $rel) {
            return null;
        }

        return preg_match('#^https?://#', $rel) ? $rel : url($rel);
    }

    public function getThumbUrlAttribute(): ?string
    {
        return $this->isImage() ? ($this->variantUrl('thumb') ?? $this->url) : null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->title ?: ($this->original_name ?: $this->file_name);
    }

    public function getSizeHumanAttribute(): string
    {
        $b = (int) $this->size;
        if ($b >= 1048576) {
            return number_format($b / 1048576, 1).' MB';
        }

        return max(1, (int) round($b / 1024)).' KB';
    }

    public function scopeOfType(Builder $q, ?string $type): Builder
    {
        return match ($type) {
            'image', 'video', 'audio' => $q->where('type', $type),
            'document', 'file' => $q->where('type', 'file'),
            default => $q,
        };
    }

    /** پیدا کردن رسانه از روی آدرس (نسبی یا مطلق، فایل اصلی یا هر نسخه) */
    public static function findByUrl(?string $url): ?self
    {
        if (! $url) {
            return null;
        }
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $stem = pathinfo(basename($path), PATHINFO_FILENAME);
        $stem = preg_replace('/-(thumb|medium|large)$/', '', $stem);
        if ($stem === '') {
            return null;
        }

        return static::where('file_name', 'like', $stem.'.%')->first();
    }

    /** ساختار JSON برای کتابخانه و انتخابگر */
    public function toPicker(): array
    {
        $u = $this->variants ?? [];

        return [
            'id' => $this->id,
            'name' => $this->display_name,
            'file_name' => $this->file_name,
            'original_name' => $this->original_name,
            'ext' => $this->extension ?: pathinfo($this->file_name, PATHINFO_EXTENSION),
            'type' => $this->type === 'file' ? 'document' : $this->type,
            'mime' => $this->mime_type,
            'size' => (int) $this->size,
            'size_h' => $this->size_human,
            'width' => $this->width,
            'height' => $this->height,
            'url' => $this->url,
            'rel' => $this->rel_url,
            'thumb' => $this->isImage() ? ($this->variantUrl('thumb') ?? $this->url) : null,
            'medium' => $this->variantUrl('medium'),
            'large' => $this->variantUrl('large'),
            'webp' => $this->variantUrl('webp'),
            'medium_rel' => $this->variantRel('medium'),
            'large_rel' => $this->variantRel('large'),
            'has_variants' => ! empty($u),
            'title' => $this->title,
            'alt' => $this->alt,
            'caption' => $this->caption,
            'date' => $this->created_at ? jdate($this->created_at)->format('Y/m/d H:i') : null,
            'by' => $this->uploader?->name,
        ];
    }
}
