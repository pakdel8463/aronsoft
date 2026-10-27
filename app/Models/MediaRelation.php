<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaRelation extends Model
{
    protected $fillable = [
        'media_id',
        'model_id',
        'model_type',
        'role',
        'sort',
    ];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

    /**
     * ثبت/همگام‌سازی استفاده‌ی یک مدل از رسانه‌ها برای یک نقش (featured, content, ...).
     *
     * @param  int[]  $mediaIds
     */
    public static function syncFor(Model $model, string $role, array $mediaIds): void
    {
        $mediaIds = array_values(array_unique(array_filter($mediaIds)));
        $base = static::where('model_type', $model->getMorphClass())->where('model_id', $model->getKey())->where('role', $role);
        (clone $base)->whereNotIn('media_id', $mediaIds ?: [0])->delete();
        $have = (clone $base)->pluck('media_id')->all();
        foreach (array_diff($mediaIds, $have) as $i => $id) {
            static::create(['media_id' => $id, 'model_type' => $model->getMorphClass(), 'model_id' => $model->getKey(), 'role' => $role, 'sort' => $i]);
        }
    }
}
