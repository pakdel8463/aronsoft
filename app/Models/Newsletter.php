<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = [
        'email',
        'token',
        'is_active',
        'confirmed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /** تأییدشده و فعال */
    public function scopeConfirmed($q)
    {
        return $q->where('is_active', true)->whereNull('unsubscribed_at');
    }

    /** منتظر تأیید ایمیل (double opt-in) */
    public function scopePending($q)
    {
        return $q->where('is_active', false)->whereNull('unsubscribed_at');
    }

    public function scopeUnsubscribed($q)
    {
        return $q->whereNotNull('unsubscribed_at');
    }

    /** @return 'confirmed'|'pending'|'unsubscribed' */
    public function getStateAttribute(): string
    {
        if ($this->unsubscribed_at) {
            return 'unsubscribed';
        }

        return $this->is_active ? 'confirmed' : 'pending';
    }
}
