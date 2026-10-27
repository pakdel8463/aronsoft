<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;
    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'count',
        'language',
        'description',
    ];

    public static function generateUniqueSlug($value, $ignoreId = null, $column = 'slug')
    {
        return \App\Support\Slugger::unique(static::class, (string) $value, $ignoreId ? (int) $ignoreId : null, $column, 'tag');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }

    public function portfolios()
    {
        return $this->belongsToMany(Portfolio::class, 'portfolio_tag');
    }
}

