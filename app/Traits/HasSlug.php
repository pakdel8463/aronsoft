<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Generate a unique slug for ANY model using this trait.
     *
     * @param string $value        عنوان یا اسلاگ پیشنهادی
     * @param mixed|null $ignoreId آی‌دی برای نادیده گرفتن (در حالت update)
     * @param string $column       نام ستون اسلاگ (پیش‌فرض: slug)
     *
     * @return string
     */
    public static function generateUniqueSlug($value, $ignoreId = null, $column = 'slug')
    {
        // اسلاگ پایه
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $counter = 1;

        // مدل جاری را تشخیص می‌دهد
        $model = new static;

        while (
        $model->newQuery()
            ->where($column, $slug)
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
