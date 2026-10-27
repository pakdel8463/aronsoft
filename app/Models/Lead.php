<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    /**
     * ستون‌هایی که اجازه پر شدن دارند
     */
    protected $fillable = [
        'name',
        'lastname',
        'company',
        'email',
        'mobile',
        'request_type',
        'message',
        'page',
        'referrer',
        'ip',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'is_contacted',
        'needs_followup',
        'admin_note',
    ];

    /**
     * Cast برای فیلدهای boolean
     */
    protected $casts = [
        'is_contacted'   => 'boolean',
        'needs_followup' => 'boolean',
    ];

    /**
     * نام کامل کاربر
     */
    public function getFullNameAttribute()
    {
        return trim($this->name . ' ' . $this->lastname);
    }

    /**
     * اسکپ: فقط لیست کسانی که هنوز پیگیری نشدن
     */
    public function scopePending($query)
    {
        return $query->where('needs_followup', true);
    }

    /**
     * اسکپ: فقط کسانی که تماس با آنها گرفته شده
     */
    public function scopeContacted($query)
    {
        return $query->where('is_contacted', true);
    }

    /** جستجو در نام، شرکت، ایمیل و موبایل */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        return $query->where(function ($w) use ($like) {
            $w->where('name', 'like', $like)
                ->orWhere('lastname', 'like', $like)
                ->orWhere('company', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('mobile', 'like', $like)
                ->orWhere('message', 'like', $like);
        });
    }
}
