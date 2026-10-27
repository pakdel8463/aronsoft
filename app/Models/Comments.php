<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    protected $fillable = ['content', 'user_id', 'guest_name', 'guest_email', 'parent_id','commentable_type','commentable_id', 'is_approved'];

    // رابطه چندشکلی
    public function commentable()
    {
        return $this->morphTo();
    }

    // رابطه برای پاسخ‌ها
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // رابطه برای دسترسی به والد یک کامنت
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // رابطه کاربر
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // بررسی کاربر مهمان
    public function isGuest()
    {
        return is_null($this->user_id);
    }
}
