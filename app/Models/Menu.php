<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'position',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort');
    }

    public const POSITIONS = [
        'top' => 'منوی اصلی (هدر)',
        'footer-col-1' => 'فوتر - ستون ۱',
        'footer-col-2' => 'فوتر - ستون ۲',
    ];

    public function getPositionLabelAttribute(): string
    {
        return self::POSITIONS[$this->position] ?? (string) $this->position;
    }

    public function tree()
    {
        // فقط آیتم‌های سطح اصلی (بدون والد) رو میگیریم
        // و فرزندانشون رو هم لود میکنیم تا کوئری اضافی نخوره
        return $this->items()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort')
            ->get();
    }

}

