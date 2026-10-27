<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class DynamicRoute extends Model
{
    protected $fillable = ['slug', 'model_type', 'model_id'];

    public function model()
    {
        return $this->morphTo();
    }
}
