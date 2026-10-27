<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public const STATUSES = ['pending' => 'در انتظار', 'approved' => 'تأییدشده', 'rejected' => 'ردشده', 'spam' => 'اسپم'];

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'name',
        'email',
        'body',
        'is_approved',
        'status',
        'ip',
        'user_agent',
    ];

    protected $casts = ['is_approved' => 'boolean'];

    protected static function booted(): void
    {
        // is_approved (که فرانت می‌خواند) و status همیشه همگام می‌مانند
        static::saving(function (Comment $c) {
            if ($c->isDirty('status')) {
                $c->is_approved = $c->status === 'approved';
            } elseif ($c->isDirty('is_approved')) {
                if ($c->is_approved) {
                    $c->status = 'approved';
                } elseif (($c->status ?? 'pending') === 'approved') {
                    $c->status = 'pending';
                }
            } elseif (! $c->exists && ! $c->status) {
                $c->status = $c->is_approved ? 'approved' : 'pending';
            }
        });
    }

    public function post()
    {
        return $this->belongsTo(Post::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('replies');
    }

    public function scopeApproved(Builder $query)
    {
        return $query->where('is_approved', true);
    }

    public function getIsAdminReplyAttribute(): bool
    {
        return (bool) $this->user_id && (bool) ($this->user?->is_staff || $this->user?->is_superuser);
    }
}
