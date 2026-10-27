<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PortfolioImage extends Model
{
    public const DIR = 'portfolio/gallery';

    protected $fillable = [
        'portfolio_id',
        'image',
        'sort',
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    /** مسیر نسبی به ریشه‌ی وب (برای Seo::img در سایت عمومی). */
    public function getPathAttribute(): string
    {
        return 'storage/'.self::DIR.'/'.$this->image;
    }

    public function getUrlAttribute(): string
    {
        return asset($this->path);
    }

    /** حذف رکورد همراه فایل. */
    protected static function booted(): void
    {
        static::deleting(function (PortfolioImage $img) {
            if ($img->image) {
                Storage::disk('public')->delete(self::DIR.'/'.basename($img->image));
            }
        });
    }
}
