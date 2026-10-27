<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['ticket_id', 'user_id', 'type', 'meta', 'created_at'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'created_at' => 'datetime'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIconAttribute(): string
    {
        return [
            'created' => 'plus-circle', 'replied' => 'chat-centered-text', 'note' => 'note-pencil', 'assigned' => 'user-switch',
            'status' => 'arrows-clockwise', 'priority' => 'flag', 'department' => 'folders', 'rated' => 'star', 'reopened' => 'arrow-counter-clockwise',
        ][$this->type] ?? 'circle';
    }

    /** توضیح فارسی رویداد برای گزارش فعالیت. */
    public function describe(): string
    {
        $m = $this->meta ?? [];

        return match ($this->type) {
            'created' => 'تیکت ثبت شد',
            'replied' => ($m['by'] ?? '') === 'staff' ? 'پشتیبان پاسخ داد' : 'مشتری پاسخ داد',
            'note' => 'یادداشت داخلی افزوده شد',
            'assigned' => empty($m['to']) ? 'ارجاع برداشته شد' : 'ارجاع به '.$m['to'],
            'status' => 'وضعیت: '.(Ticket::STATUS_LABELS[$m['from'] ?? ''] ?? '—').' ← '.(Ticket::STATUS_LABELS[$m['to'] ?? ''] ?? '—'),
            'priority' => 'اولویت: '.(Ticket::PRIORITY_LABELS[$m['from'] ?? ''] ?? '—').' ← '.(Ticket::PRIORITY_LABELS[$m['to'] ?? ''] ?? '—'),
            'department' => 'دپارتمان: '.($m['from'] ?? '—').' ← '.($m['to'] ?? '—'),
            'rated' => 'مشتری امتیاز '.\App\Helpers\Fa::toFa($m['rating'] ?? '').' از ۵ داد',
            'reopened' => 'تیکت دوباره باز شد',
            default => $this->type,
        };
    }
}
