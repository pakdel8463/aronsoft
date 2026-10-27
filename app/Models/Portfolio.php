<?php

namespace App\Models;

use App\Casts\TechList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Portfolio extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'status',
        'seo_title',
        'seo_description',
        'seo_score',
        'featured_image',
        'technologies',
        'client_name',
        'start_date',
        'end_date',
        'preview_link',
        'progress',
    ];

    protected $casts = [
        'technologies' => TechList::class,
        'start_date'   => 'date',
        'end_date'     => 'date',
        'progress'     => 'integer',
        'seo_score'    => 'integer',
    ];

    public const STATUSES = [
        'published' => 'منتشر شده',
        'draft'     => 'پیش‌نویس',
        'private'   => 'خصوصی (نمایش داده نمی‌شود)',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_portfolio');
    }

    /** تگ‌ها (many-to-many) */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'portfolio_tag');
    }

    /** گالری تصاویر (one-to-many)، مرتب بر اساس sort */
    public function images()
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('sort')->orderBy('id');
    }

    public function teamMembers()
    {
        return $this->belongsToMany(TeamMember::class, 'portfolio_team_member', 'portfolio_id', 'team_member_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /** اسلاگ یکتا (مثل بقیه‌ی سایت: لاتین‌شده). */
    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'project';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** امتیاز ساده سئو (۰ تا ۱۰۰) از روی فیلدهای پر شده. */
    public static function computeSeoScore(array $d): int
    {
        $score = 0;
        $t = mb_strlen(trim((string) ($d['seo_title'] ?? '')));
        $s = mb_strlen(trim((string) ($d['seo_description'] ?? '')));
        $score += $t >= 25 && $t <= 65 ? 30 : ($t > 0 ? 15 : 0);
        $score += $s >= 70 && $s <= 170 ? 30 : ($s > 0 ? 15 : 0);
        $score += ! empty($d['featured_image']) ? 20 : 0;
        $score += mb_strlen(strip_tags((string) ($d['description'] ?? ''))) >= 300 ? 20 : 0;

        return $score;
    }
}
