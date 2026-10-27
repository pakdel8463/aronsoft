<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    public const IMAGE_DIR = 'products';
    public const FILE_DIR = 'products/files';

    public const TYPES = [
        'wordpress_theme'  => 'قالب وردپرس',
        'wordpress_plugin' => 'افزونه وردپرس',
        'laravel_package'  => 'پکیج لاراول',
        'linux_script'     => 'اسکریپت لینوکس',
    ];

    protected $fillable = [
        'title',
        'slug',
        'type',
        'short_description',
        'description',
        'price',
        'discount_price',
        'featured_image',
        'gallery',
        'file_path',
        'download_url',
        'demo_link',
        'is_for_sale',
        'is_active',
        'version',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
    ];

    protected $casts = [
        'price'          => 'integer',
        'discount_price' => 'integer',
        'gallery'        => 'array',
        'is_for_sale'    => 'boolean',
        'is_active'      => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (! $product->slug) {
                $product->slug = static::uniqueSlug($product->title);
            }
        });
    }

    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'product';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    // هلپر برای تشخیص رایگان بودن
    public function isFree()
    {
        return $this->price <= 0;
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price > 0 && $this->discount_price < $this->price;
    }

    public function finalPrice(): int
    {
        return (int) ($this->hasDiscount() ? $this->discount_price : $this->price);
    }

    /** فقط فایل‌های ساخته‌شده توسط خود پنل پاک می‌شوند (نه دمو/کتابخانه‌ی رسانه). */
    public static function deleteManagedImage(?string $path): void
    {
        $prefix = 'storage/'.self::IMAGE_DIR.'/';
        if ($path && str_starts_with($path, $prefix) && ! str_contains($path, '..')) {
            Storage::disk('public')->delete(self::IMAGE_DIR.'/'.basename($path));
        }
    }

    public static function deleteManagedFile(?string $path): void
    {
        if ($path && str_starts_with($path, self::FILE_DIR.'/') && ! str_contains($path, '..')) {
            Storage::disk('local')->delete($path);
        }
    }
}
