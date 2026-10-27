<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Order extends Model
{
    public const STATUS_NEW = 'new';

    protected $fillable = [
        'code', 'access_token', 'user_id', 'lead_id',
        'type', 'budget', 'deadline', 'features', 'description',
        'attachment_path', 'attachment_name',
        'name', 'mobile', 'email', 'contact_time', 'consented_at',
        'status', 'admin_note',
        'ip', 'user_agent', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign',
    ];

    protected $hidden = ['access_token'];

    protected $casts = [
        'features' => 'array',
        'consented_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->code ??= self::generateCode();
            $order->access_token ??= Str::random(32);
            $order->status ??= self::STATUS_NEW;
        });

        static::deleting(function (Order $order) {
            if ($order->attachment_path) {
                Storage::disk('local')->delete($order->attachment_path);
            }
        });
    }

    /** ARN-XXXXXX (no ambiguous characters) */
    public static function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = 'ARN-';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /* ---------- relations ---------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /* ---------- labels ---------- */

    public static function statuses(): array
    {
        return config('aron_services.order.statuses');
    }

    public static function types(): array
    {
        return collect(config('aron_services.order.types'))->map(fn ($t) => $t['label'])->all();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    /** رنگ نشان (x-admin.badge) برای وضعیت */
    public function getStatusToneAttribute(): string
    {
        return self::tone($this->status);
    }

    public static function tone(?string $status): string
    {
        return match ($status) {
            'new' => 'brand',
            'contacted' => 'sky',
            'quoted' => 'amber',
            'won' => 'mint',
            'lost' => 'coral',
            default => 'slate',
        };
    }

    public function events()
    {
        return $this->hasMany(OrderEvent::class)->latest('created_at')->latest('id');
    }

    /** bootstrap-like colour keyword for simple admin badges (legacy) */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new' => 'primary',
            'contacted' => 'info',
            'quoted' => 'warning',
            'won' => 'success',
            'lost' => 'danger',
            default => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return config("aron_services.order.types.{$this->type}.label", $this->type);
    }

    public function getBudgetLabelAttribute(): ?string
    {
        return $this->budget ? (config("aron_services.order.budgets.{$this->budget}") ?? $this->budget) : null;
    }

    public function getDeadlineLabelAttribute(): ?string
    {
        return $this->deadline ? (config("aron_services.order.deadlines.{$this->deadline}") ?? $this->deadline) : null;
    }

    public function getContactTimeLabelAttribute(): ?string
    {
        return $this->contact_time ? (config("aron_services.order.contact_times.{$this->contact_time}") ?? $this->contact_time) : null;
    }

    /** @return array<int,string> human readable feature labels */
    public function getFeatureLabelsAttribute(): array
    {
        $map = config("aron_services.order.types.{$this->type}.features", []);

        return collect($this->features ?? [])->map(fn ($k) => $map[$k] ?? $k)->values()->all();
    }

    public function publicUrl(): string
    {
        return route('order.show', ['code' => $this->code, 't' => $this->access_token]);
    }

    /* ---------- scopes ---------- */

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeOfType(Builder $q, ?string $type): Builder
    {
        return $type ? $q->where('type', $type) : $q;
    }

    public function scopeFresh(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_NEW);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        return $q->where(function (Builder $w) use ($like) {
            $w->where('code', 'like', $like)
                ->orWhere('name', 'like', $like)
                ->orWhere('mobile', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }
}
