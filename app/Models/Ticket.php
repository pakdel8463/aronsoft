<?php

namespace App\Models;

use App\Helpers\Fa;
use App\Helpers\Notifier;
use App\Mail\TicketReplyMail;
use App\Mail\TicketStaffNotificationMail;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use Throwable;

class Ticket extends Model
{
    public const OPEN = 'open';                       // جدید، هنوز پاسخی نگرفته
    public const WAITING_ADMIN = 'waiting_admin';     // مشتری پاسخ داده، منتظر پشتیبان
    public const WAITING_CUSTOMER = 'waiting_customer'; // پشتیبان پاسخ داده، منتظر مشتری
    public const CLOSED = 'closed';

    public const STATUS_LABELS = [
        self::OPEN => 'جدید',
        self::WAITING_ADMIN => 'منتظر پشتیبان',
        self::WAITING_CUSTOMER => 'منتظر مشتری',
        self::CLOSED => 'بسته‌شده',
    ];

    public const PRIORITY_LABELS = ['low' => 'کم', 'normal' => 'معمولی', 'high' => 'مهم', 'urgent' => 'فوری'];

    protected $fillable = [
        'number', 'user_id', 'department_id', 'assigned_to', 'subject', 'status', 'priority', 'order_code',
        'client_unread', 'staff_unread', 'first_response_at', 'last_reply_at', 'last_reply_by', 'closed_at',
        'rating', 'rating_comment', 'rated_at', 'ip',
    ];

    protected function casts(): array
    {
        return [
            'client_unread' => 'boolean',
            'staff_unread' => 'boolean',
            'first_response_at' => 'datetime',
            'last_reply_at' => 'datetime',
            'closed_at' => 'datetime',
            'rated_at' => 'datetime',
            'rating' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $t) {
            // شمارهٔ موقت یکتا؛ بلافاصله بعد از ایجاد با شمارهٔ نهایی (بر پایهٔ id) جایگزین می‌شود
            $t->number ??= 'TMP-'.Str::upper(Str::random(12));
        });

        static::created(function (Ticket $t) {
            if (str_starts_with($t->number, 'TMP-')) {
                $t->number = config('tickets.prefix').(config('tickets.start') + $t->id);
                $t->saveQuietly();
            }
        });

        static::deleting(function (Ticket $t) {
            Storage::disk(config('tickets.attachments.disk'))->deleteDirectory('tickets/'.$t->id);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    /* ---------------------------------------------------------------- relations */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TicketDepartment::class, 'department_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketEvent::class)->orderBy('created_at')->orderBy('id');
    }

    /* ---------------------------------------------------------------- scopes */

    public function scopeNotClosed(Builder $q): Builder
    {
        return $q->where('status', '!=', self::CLOSED);
    }

    public function scopeNeedsStaff(Builder $q): Builder
    {
        return $q->whereIn('status', [self::OPEN, self::WAITING_ADMIN]);
    }

    /** جستجو در شماره، موضوع، نام و ایمیل کاربر. */
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) Fa::digits($term));
        if ($term === '') {
            return $q;
        }
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        return $q->where(function (Builder $w) use ($like) {
            $w->where('number', 'like', $like)
                ->orWhere('subject', 'like', $like)
                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
        });
    }

    /* ---------------------------------------------------------------- labels */

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /** برچسب وضعیت از دید مشتری. */
    public function getClientStatusLabelAttribute(): string
    {
        return [
            self::OPEN => 'در انتظار بررسی',
            self::WAITING_ADMIN => 'در حال بررسی',
            self::WAITING_CUSTOMER => 'پاسخ داده شده',
            self::CLOSED => 'بسته‌شده',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return [self::OPEN => 'sky', self::WAITING_ADMIN => 'coral', self::WAITING_CUSTOMER => 'amber', self::CLOSED => 'slate'][$this->status] ?? 'slate';
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITY_LABELS[$this->priority] ?? $this->priority;
    }

    public function getPriorityColorAttribute(): string
    {
        return ['low' => 'slate', 'normal' => 'sky', 'high' => 'amber', 'urgent' => 'coral'][$this->priority] ?? 'slate';
    }

    public function isClosed(): bool
    {
        return $this->status === self::CLOSED;
    }

    public function canReopen(): bool
    {
        if (! $this->isClosed()) {
            return false;
        }
        $days = (int) config('tickets.reopen_days', 14);

        return $days <= 0 || ($this->closed_at && $this->closed_at->gt(now()->subDays($days)));
    }

    /* ---------------------------------------------------------------- creating / replying */

    /**
     * ثبت تیکت جدید با پیام اول و پیوست‌ها. $data: subject, body, department_id, priority, order_code.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public static function openFor(User $user, array $data, array $files = [], ?string $ip = null): self
    {
        $ticket = DB::transaction(function () use ($user, $data, $files, $ip) {
            $ticket = self::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'] ?? null,
                'subject' => $data['subject'],
                'priority' => $data['priority'] ?? 'normal',
                'order_code' => $data['order_code'] ?? null,
                'status' => self::OPEN,
                'staff_unread' => true,
                'client_unread' => false,
                'last_reply_at' => now(),
                'last_reply_by' => 'customer',
                'ip' => $ip,
            ]);

            $message = $ticket->messages()->create(['user_id' => $user->id, 'type' => TicketMessage::CLIENT, 'body' => $data['body']]);
            $ticket->storeFiles($message, $files);
            $ticket->logEvent('created', $user);

            return $ticket;
        });

        $ticket->notifyStaff($ticket->messages()->first(), true);

        return $ticket;
    }

    /**
     * افزودن پیام (client | staff | note) و به‌روزرسانی وضعیت تیکت.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public function postMessage(User $author, string $type, string $body, array $files = []): TicketMessage
    {
        $message = DB::transaction(function () use ($author, $type, $body, $files) {
            $message = $this->messages()->create(['user_id' => $author->id, 'type' => $type, 'body' => $body]);
            $this->storeFiles($message, $files);

            if ($type === TicketMessage::NOTE) {
                $this->logEvent('note', $author);

                return $message;
            }

            $from = $this->status;
            if ($type === TicketMessage::STAFF) {
                $this->forceFill([
                    'status' => self::WAITING_CUSTOMER,
                    'first_response_at' => $this->first_response_at ?? now(),
                    'client_unread' => true,
                    'staff_unread' => false,
                    'last_reply_by' => 'staff',
                ]);
            } else {
                $this->forceFill([
                    'status' => self::WAITING_ADMIN,
                    'staff_unread' => true,
                    'last_reply_by' => 'customer',
                ]);
            }
            $this->forceFill(['last_reply_at' => now(), 'closed_at' => null])->save();

            $this->logEvent('replied', $author, ['by' => $type === TicketMessage::STAFF ? 'staff' : 'customer']);
            if ($from !== $this->status) {
                $this->logEvent('status', $author, ['from' => $from, 'to' => $this->status]);
            }

            return $message;
        });

        if ($type === TicketMessage::STAFF) {
            $this->notifyClient($message);
        } elseif ($type === TicketMessage::CLIENT) {
            $this->notifyStaff($message, false);
        }

        return $message;
    }

    /** @param array<int, UploadedFile> $files */
    protected function storeFiles(TicketMessage $message, array $files): void
    {
        foreach (array_slice(array_filter($files), 0, (int) config('tickets.attachments.max_files', 3)) as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                TicketAttachment::storeUpload($message, $file);
            }
        }
    }

    public function changeStatus(string $to, ?User $by = null): void
    {
        if (! isset(self::STATUS_LABELS[$to]) || $to === $this->status) {
            return;
        }
        $from = $this->status;
        $this->forceFill([
            'status' => $to,
            'closed_at' => $to === self::CLOSED ? now() : null,
        ])->save();
        $this->logEvent($from === self::CLOSED ? 'reopened' : 'status', $by, ['from' => $from, 'to' => $to]);
    }

    public function close(?User $by = null): void
    {
        $this->changeStatus(self::CLOSED, $by);
    }

    /** بازگشایی توسط مشتری: به صف پشتیبان برمی‌گردد. */
    public function reopen(User $by): void
    {
        $this->changeStatus(self::WAITING_ADMIN, $by);
        $this->forceFill(['staff_unread' => true, 'rating' => null, 'rating_comment' => null, 'rated_at' => null])->save();
    }

    public function logEvent(string $type, ?User $by = null, array $meta = []): TicketEvent
    {
        return $this->events()->create([
            'user_id' => $by?->id,
            'type' => $type,
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }

    /* ---------------------------------------------------------------- SLA */

    public function slaHours(): int
    {
        $cfg = config('tickets.sla.first_response_hours', []);

        return (int) ($cfg[$this->priority] ?? $cfg['normal'] ?? 24);
    }

    /**
     * وضعیت SLA پاسخ اول: ['state' => met|breached|ok|warning|overdue, 'label' => ..., 'color' => ...] یا null.
     */
    public function sla(): ?array
    {
        $due = $this->created_at->copy()->addHours($this->slaHours());

        if ($this->first_response_at) {
            $met = $this->first_response_at->lte($due);

            return ['state' => $met ? 'met' : 'breached', 'label' => $met ? 'SLA رعایت شد' : 'SLA رعایت نشد', 'color' => $met ? 'mint' : 'slate'];
        }
        if ($this->isClosed()) {
            return null;
        }

        $now = now();
        if ($now->gt($due)) {
            return ['state' => 'overdue', 'label' => 'تأخیر '.self::duration($due, $now), 'color' => 'coral'];
        }
        $left = $now->diffInMinutes($due);
        $window = max(1, $this->slaHours() * 60);

        return [
            'state' => $left / $window < 0.25 ? 'warning' : 'ok',
            'label' => 'مهلت '.self::duration($now, $due),
            'color' => $left / $window < 0.25 ? 'amber' : 'mint',
        ];
    }

    public static function duration(CarbonInterface $a, CarbonInterface $b): string
    {
        $min = (int) abs($a->diffInMinutes($b));
        if ($min < 60) {
            return Fa::toFa(max(1, $min)).' دقیقه';
        }
        if ($min < 60 * 48) {
            return Fa::toFa((int) round($min / 60)).' ساعت';
        }

        return Fa::toFa((int) round($min / 1440)).' روز';
    }

    /* ---------------------------------------------------------------- dates (Persian) */

    public static function jdate(?CarbonInterface $d, string $format = 'Y/m/d - H:i'): string
    {
        return $d ? Fa::toFa(Jalalian::fromCarbon($d)->format($format)) : '—';
    }

    /** «۵ دقیقه پیش» تا یک هفته، بعد از آن تاریخ شمسی. */
    public static function ago(?CarbonInterface $d): string
    {
        if (! $d) {
            return '—';
        }
        $s = (int) $d->diffInSeconds(now());
        if ($d->isFuture() || $s < 60) {
            return 'چند لحظه پیش';
        }
        if ($s < 3600) {
            return Fa::toFa((int) floor($s / 60)).' دقیقه پیش';
        }
        if ($s < 86400) {
            return Fa::toFa((int) floor($s / 3600)).' ساعت پیش';
        }
        if ($s < 7 * 86400) {
            return Fa::toFa((int) floor($s / 86400)).' روز پیش';
        }

        return self::jdate($d, 'j F Y');
    }

    /* ---------------------------------------------------------------- misc helpers */

    /** تعداد تیکت‌های دارای پاسخ خوانده‌نشده برای کاربر (نشانگر داشبورد). */
    public static function unreadCountFor(?User $user): int
    {
        if (! $user) {
            return 0;
        }
        try {
            return Schema::hasTable('tickets')
                ? (int) self::where('user_id', $user->id)->where('client_unread', true)->count()
                : 0;
        } catch (Throwable) {
            return 0;
        }
    }

    /** کاربرانی که می‌توان تیکت را به آن‌ها ارجاع داد. */
    public static function staffUsers()
    {
        return User::query()->where(fn ($q) => $q->where('is_staff', true)->orWhere('is_superuser', true))->orderBy('name')->get(['id', 'name', 'email']);
    }

    /* ---------------------------------------------------------------- notifications (never throw) */

    public function staffRecipient(): ?string
    {
        return $this->department?->email ?: (config('tickets.notify_email') ?: config('mail.admin_address'));
    }

    public function notifyStaff(?TicketMessage $message, bool $isNew): void
    {
        if (! $message) {
            return;
        }
        $ticket = $this;
        dispatch(function () use ($ticket, $message, $isNew) {
            try {
                $ticket->loadMissing('user', 'department');
                Notifier::to($ticket->staffRecipient(), new TicketStaffNotificationMail($ticket, $message, $isNew));
                if ($isNew) {
                    Notifier::telegram("تیکت جدید {$ticket->number}\n{$ticket->subject}\n".route('panel.tickets.show', $ticket));
                }
            } catch (Throwable $e) {
                report($e);
            }
        })->afterResponse();
    }

    public function notifyClient(TicketMessage $message): void
    {
        $ticket = $this;
        dispatch(function () use ($ticket, $message) {
            try {
                $ticket->loadMissing('user');
                Notifier::to($ticket->user?->email, new TicketReplyMail($ticket, $message));
            } catch (Throwable $e) {
                report($e);
            }
        })->afterResponse();
    }
}
