<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'province',
        'city',
        'address',
        'note',
        'avatar',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** نشانی قابل‌نمایش آواتار (فایل public disk، مسیر storage/ یا URL کامل) یا null */
    public function getAvatarUrlAttribute(): ?string
    {
        $a = $this->avatar;
        if (! $a) {
            return null;
        }
        if (preg_match('#^(https?:)?//#', $a) || str_starts_with($a, '/')) {
            return $a;
        }

        return asset(str_starts_with($a, 'storage/') ? $a : 'storage/'.$a);
    }
}
