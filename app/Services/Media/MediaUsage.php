<?php

namespace App\Services\Media;

use App\Models\Category;
use App\Models\Media;
use App\Models\MediaRelation;
use App\Models\Post;
use Illuminate\Support\Facades\Schema;

/** پیدا کردن جاهایی که یک رسانه استفاده شده (media_relations + جستجوی آدرس در پست‌ها/دسته‌ها/نمونه‌کارها) */
class MediaUsage
{
    /** @return array<int,array{type:string,label:string,url:?string}> */
    public static function for(Media $media): array
    {
        $out = [];
        $seen = [];
        $add = function (string $type, string $label, ?string $url, string $key) use (&$out, &$seen) {
            if (isset($seen[$key])) {
                return;
            }
            $seen[$key] = 1;
            $out[] = ['type' => $type, 'label' => $label, 'url' => $url];
        };

        foreach (MediaRelation::where('media_id', $media->id)->get() as $rel) {
            $m = $rel->model_type::query()->find($rel->model_id);
            if ($m instanceof Post) {
                $add('post', $m->title, route('panel.posts.edit', $m->id), 'post'.$m->id);
            } elseif ($m) {
                $add(class_basename($rel->model_type), method_exists($m, 'getAttribute') ? (string) ($m->title ?? $m->name ?? '#'.$m->getKey()) : '#'.$rel->model_id, null, $rel->model_type.$rel->model_id);
            }
        }

        $stem = pathinfo($media->file_name, PATHINFO_FILENAME);
        if ($stem === '') {
            return $out;
        }
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $stem).'%';

        Post::withTrashed()
            ->where(fn ($q) => $q->where('featured_image', 'like', $like)->orWhere('og_image', 'like', $like)->orWhere('body', 'like', $like))
            ->limit(30)->get(['id', 'title'])
            ->each(fn ($p) => $add('post', $p->title, route('panel.posts.edit', $p->id), 'post'.$p->id));

        Category::where('image', 'like', $like)->limit(20)->get(['id', 'name'])
            ->each(fn ($c) => $add('category', 'دسته: '.$c->name, route('panel.categories.edit', $c->id), 'cat'.$c->id));

        foreach (['portfolios' => ['featured_image', 'title'], 'products' => ['image', 'name']] as $table => [$col, $label]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $col) && Schema::hasColumn($table, $label)) {
                \DB::table($table)->where($col, 'like', $like)->limit(20)->get(['id', $label])
                    ->each(fn ($r) => $add($table, ($table === 'portfolios' ? 'نمونه‌کار: ' : 'محصول: ').$r->{$label}, null, $table.$r->id));
            }
        }

        return $out;
    }
}
