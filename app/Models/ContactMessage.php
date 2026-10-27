<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'subject',
        'message',
        'budget',
        'preferred_contact_method',
        'preferred_contact_time',
        'status',
        'ip_address',
        'user_agent',
        'referrer',
        'page_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'is_spam',
    ];

    protected $casts = [
        'is_spam' => 'boolean',
    ];

    public const STATUSES = [
        'new' => 'جدید',
        'reviewed' => 'دیده شد',
        'contacted' => 'تماس گرفته شد',
        'closed' => 'بسته شد',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? (string) $this->status;
    }

}

