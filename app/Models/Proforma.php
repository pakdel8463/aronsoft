<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proforma extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
    ];

    public function publicUrl(): string
    {
        return route('proforma.show', $this->uuid);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
