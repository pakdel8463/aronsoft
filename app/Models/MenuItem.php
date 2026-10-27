<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'menu_id', 'parent_id', 'user_id',
        'display_title', 'icon', 'status',
        'lang', 'url', 'model', 'model_id',
        'sort', 'depth'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort');
    }
    public function getHrefAttribute()
    {
        // ۱. اگر لینک دستی (Custom) است
        if (empty($this->model)) {
            return $this->url;
        }

        // ۲. اگر لینک داینامیک است (مربوط به یک مدل)
        try {
            // اینجا فرض می‌کنیم مدل‌های شما یک فیلد slug یا متدی برای لینک دارند
            // مثلا اگر Post باشد:
            if ($this->model === 'App\Models\Post') {
                $post = Post::find($this->model_id);
                return $post ? url('blog/' . $post->slug) : '#';
            }

            // اگر LandingPage باشد
            if ($this->model === 'App\Models\LandingPage') {
                $page = LandingPage::find($this->model_id);
                return $page ? url('services/' . $page->slug) : '#';
            }

            if ($this->model === 'App\Models\ContactMessage') {
                return url('contact');
            }
            // برای سایر مدل‌ها هم همینطور...
            // اگر مدلی پیدا نشد همون url ذخیره شده رو برگردون
            return $this->url;

        } catch (\Exception $e) {
            return '#';
        }
    }
}

