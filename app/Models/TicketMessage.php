<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;

class TicketMessage extends Model
{
    public const CLIENT = 'client';
    public const STAFF = 'staff';
    public const NOTE = 'note';

    protected $fillable = ['ticket_id', 'user_id', 'type', 'body'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    /** پیام‌هایی که مشتری می‌بیند (بدون یادداشت داخلی). */
    public function scopeVisibleToClient(Builder $q): Builder
    {
        return $q->where('type', '!=', self::NOTE);
    }

    public function isNote(): bool
    {
        return $this->type === self::NOTE;
    }

    public function isStaff(): bool
    {
        return $this->type === self::STAFF;
    }

    /**
     * متن امن برای نمایش: ابتدا کاملاً escape می‌شود، سپس فقط آدرس‌های http(s) به لینک تبدیل می‌شوند.
     */
    public function getHtmlAttribute(): HtmlString
    {
        return self::render((string) $this->body);
    }

    public static function render(string $text): HtmlString
    {
        $safe = e($text);
        $safe = preg_replace_callback(
            '~\bhttps?://(?:(?!&lt;|&gt;|&quot;|&#039;)[^\s<>"\'])+~iu',
            function (array $m) {
                $url = $m[0];
                $tail = '';
                // علائم نگارشی انتهای جمله جزو لینک نیستند (. , ; : ! ? ) ، ؛ ؟)
                if (preg_match('~(?:&amp;|[.,;:!?)\]،؛؟])+$~u', $url, $t)) {
                    $tail = $t[0];
                    $url = substr($url, 0, -strlen($tail));
                }
                if (strlen($url) < 10) {
                    return $m[0];
                }

                return '<a href="'.$url.'" target="_blank" rel="nofollow noopener noreferrer ugc" dir="ltr" class="underline underline-offset-2 break-all">'.$url.'</a>'.$tail;
            },
            $safe
        );

        return new HtmlString(nl2br((string) $safe, false));
    }
}
