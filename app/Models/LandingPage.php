<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'template',
        'is_active',
        'seo_title',
        'seo_description',
        'created_by', 'portfolio_id', 'sections', 'draft_sections', 'appearance', 'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean', 'sections' => 'array', 'draft_sections' => 'array', 'appearance' => 'array', 'published_at' => 'datetime',
    ];

    /** قالب‌های موجود = فایل‌های blade عمومی در front/t-one/landings (به‌جز فایل‌های فرعی/مخفی). */
    public static function templates(): array
    {
        $labels = [
            'wordpress' => 'طراحی سایت وردپرس',
            'webdev'    => 'توسعه وب / سایت اختصاصی',
            'devops'    => 'DevOps و زیرساخت',
            'seo'       => 'سئو و بهینه‌سازی',
            'about'     => 'درباره ما',
        ];
        $out = [];
        foreach (glob(resource_path('views/front/t-one/landings/*.blade.php')) ?: [] as $file) {
            $name = basename($file, '.blade.php');
            if (str_starts_with($name, 'inv-') || str_starts_with($name, 'aron-')) {
                continue;
            }
            $out[$name] = $labels[$name] ?? $name;
        }
        ksort($out);

        return $out;
    }

    public function portfolio() { return $this->belongsTo(Portfolio::class); }

    // رابطه: سازنده لندینگ
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // دسترسی سریع از طریق اسلاگ
    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
