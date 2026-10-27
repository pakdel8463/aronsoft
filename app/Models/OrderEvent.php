<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** رویداد تاریخچه سفارش: تغییر وضعیت یا ثبت یادداشت */
class OrderEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'user_id', 'type', 'from', 'to', 'note', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(fn (OrderEvent $e) => $e->created_at ??= now());
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
