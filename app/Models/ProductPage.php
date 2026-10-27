<?php

namespace App\Models;

use App\Support\ProductPageSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * صفحه‌ی لندینگ یک محصول (کالریتو، مانیار، هوماواچ …).
 * ساختار بخش‌ها: config/product_pages.php — دسترسی از طریق section() و layoutKeys().
 */
class ProductPage extends Model
{
    public const STATUSES = ['draft' => 'پیش‌نویس', 'published' => 'منتشرشده'];

    protected $fillable = [
        'slug', 'template', 'name', 'name_en', 'tagline', 'category_label', 'status', 'sort_order',
        'accent', 'accent_2', 'logo', 'icon', 'card_image', 'links', 'layout', 'sections',
        'seo_title', 'seo_description', 'seo_keywords', 'canonical_url', 'og_image', 'noindex',
        'published_at', 'created_by',
    ];

    protected $casts = [
        'links' => 'array',
        'layout' => 'array',
        'sections' => 'array',
        'noindex' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $p) {
            if (! $p->slug) {
                $p->slug = static::uniqueSlug($p->name_en ?: $p->name);
            }
        });
    }

    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base, '-', 'fa') ?: 'product';
        $slug = mb_substr($slug, 0, 100);
        $try = $slug;
        $i = 2;
        while (static::where('slug', $try)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $try = $slug.'-'.$i++;
        }

        return $try;
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')
            ->where(fn ($w) => $w->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function isLive(): bool
    {
        return $this->status === 'published' && (! $this->published_at || $this->published_at->lte(now()));
    }

    public function publicUrl(): string
    {
        return route('product-pages.show', $this->slug);
    }

    /** محتوای یک بخش (با مقدارهای پیش‌فرض اسکیما) */
    public function section(string $key): array
    {
        $def = ProductPageSchema::section($key, $this->template);
        if (! $def) {
            return [];
        }
        $stored = (array) (($this->sections ?? [])[$key] ?? []);
        $out = [];
        foreach ($def['fields'] as $f) {
            $out[$f['key']] = array_key_exists($f['key'], $stored)
                ? $stored[$f['key']]
                : ProductPageSchema::cleanValue(null, $f);
        }

        return $out;
    }

    /** کلید قالب معتبر این صفحه */
    public function templateKey(): string
    {
        return ProductPageSchema::templateKey($this->template);
    }

    /** نام ویوی قالب این صفحه */
    public function templateView(): string
    {
        return 'front.t-one.apps.templates.'.$this->templateKey();
    }

    /** رنگ‌های قالب این صفحه: [token => '#RRGGBB'] */
    public function palette(): array
    {
        return ProductPageSchema::paletteValues($this->templateKey(), (array) (($this->sections ?? [])['_palette'] ?? []));
    }

    /** مقدار style برای عنصر ریشه‌ی قالب: --t-{token}:#hex; */
    public function paletteStyle(): string
    {
        $css = '';
        foreach ($this->palette() as $k => $hex) {
            $css .= '--t-'.str_replace('_', '-', $k).':'.$hex.';';
        }

        return $css;
    }

    /** چیدمان کامل (کلید + فعال بودن) به ترتیب نمایش */
    public function layoutRows(): array
    {
        $t = $this->templateKey();

        return ProductPageSchema::normalizeLayout($this->layout ?: ProductPageSchema::defaultLayout($t), $t);
    }

    /** کلید بخش‌های فعال و دارای محتوا به ترتیب نمایش */
    public function layoutKeys(): array
    {
        $keys = [];
        foreach ($this->layoutRows() as $row) {
            if ($row['enabled'] && $this->sectionHasContent($row['key'])) {
                $keys[] = $row['key'];
            }
        }

        return $keys;
    }

    /** بخش «محتوا دارد» اگر دست‌کم یک فیلد متنی/لیستی/تصویری پر باشد (هیرو و CTA همیشه) */
    public function sectionHasContent(string $key): bool
    {
        if (in_array($key, ['hero', 'cta'], true)) {
            return true;
        }
        $def = ProductPageSchema::section($key, $this->template);
        if (! $def) {
            return false;
        }
        if (! empty($def['always'])) {
            return true;
        }
        $s = $this->section($key);
        foreach ($def['fields'] as $f) {
            if (in_array($f['type'], ['toggle', 'select', 'icon'], true)) {
                continue;
            }
            $v = $s[$f['key']] ?? null;
            if (is_array($v) ? $v !== [] : trim((string) $v) !== '') {
                return true;
            }
        }

        return false;
    }

    /** پیوند یک دکمه/استور ("website", "android" ...) یا null */
    public function link(string $key): ?string
    {
        $v = trim((string) (($this->links ?? [])[$key] ?? ''));

        return $v !== '' ? $v : null;
    }

    /** پیوندهای پر شده: [key => [url, label, icon]] */
    public function filledLinks(): array
    {
        $out = [];
        foreach (ProductPageSchema::linkTypes() as $key => [$label, $icon]) {
            if ($url = $this->link($key)) {
                $out[$key] = ['url' => $url, 'label' => $label, 'icon' => $icon];
            }
        }

        return $out;
    }

    /** لینک اصلی محصول (برای دکمه‌های بدون لینک): وب‌سایت > ورود > دمو > اولین پیوند > فرم */
    public function primaryUrl(): string
    {
        foreach (['website', 'login', 'demo'] as $k) {
            if ($u = $this->link($k)) {
                return $u;
            }
        }

        return array_values($this->filledLinks())[0]['url'] ?? '#lead';
    }

    public function logoUrl(): ?string
    {
        return ProductPageSchema::imageUrl($this->logo);
    }

    public function cardImageUrl(): ?string
    {
        return ProductPageSchema::imageUrl($this->card_image);
    }

    public function accentColor(): string
    {
        return self::cleanHex($this->accent) ?? '#2F4FF5';
    }

    public function accent2Color(): string
    {
        return self::cleanHex($this->accent_2) ?? $this->accentColor();
    }

    public static function cleanHex(?string $v): ?string
    {
        $v = trim((string) $v);

        return preg_match('/^#[0-9a-fA-F]{6}$/', $v) ? strtoupper($v) : null;
    }

    /** برای متن روی رنگ برند: سفید یا تیره */
    public function onAccent(): string
    {
        [$r, $g, $b] = sscanf($this->accentColor(), '#%02x%02x%02x');
        $lum = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $lum > 0.72 ? '#0F172A' : '#FFFFFF';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
