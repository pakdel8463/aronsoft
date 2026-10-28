<?php

namespace App\Pages;

use App\Models\Block;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/** ساخت و نگهداری صفحه‌ها، بلوک‌ها و قالب‌های صفحه‌ساز. */
final class PageBuilder
{
    public const CATEGORIES = [
        'hero' => 'هیرو و سربرگ',
        'content' => 'متن و محتوا',
        'features' => 'خدمات و ویژگی‌ها',
        'social' => 'اعتماد، آمار و قیمت',
        'dynamic' => 'محتوای خودکار سایت',
        'cta' => 'فراخوان و فرم',
        'media' => 'تصویر و ویدئو',
        'layout' => 'چیدمان و کد',
    ];

    /** کتابخانه‌ی بلوک‌ها گروه‌بندی‌شده بر اساس دسته: [category => [type => definition]] */
    public static function library(): array
    {
        $out = array_fill_keys(array_keys(self::CATEGORIES), []);
        foreach ((array) config('blocks', []) as $type => $def) {
            $out[$def['category'] ?? 'layout'][$type] = $def;
        }

        return array_filter($out);
    }

    public static function addBlock(Page $page, string $type, array $overrides = [], ?int $sort = null): Block
    {
        $def = (array) config('blocks.'.$type, []);
        $data = BlockSchema::sanitize($def['fields'] ?? [], array_replace((array) ($def['defaults'] ?? []), $overrides));

        return $page->blocks()->create([
            'type' => $type,
            'anchor' => self::uniqueAnchor($page, $type),
            'data' => $data,
            'is_active' => true,
            'sort' => $sort ?? ((int) $page->blocks()->max('sort') + 1),
        ]);
    }

    /** شناسه‌ی لنگر یکتا در صفحه (حروف لاتین کوچک، عدد و خط تیره). */
    public static function uniqueAnchor(Page $page, string $wanted, ?int $ignoreId = null): string
    {
        $base = Str::slug(str_replace('_', '-', $wanted)) ?: 'block';
        $base = Str::limit($base, 60, '');
        $anchor = $base;
        $i = 2;
        while ($page->blocks()->where('anchor', $anchor)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $anchor = $base.'-'.$i++;
        }

        return $anchor;
    }

    /** صفحه‌ی تازه، اختیاری از روی یک قالب (یا صفحه‌ی دیگر). */
    public static function createPage(string $title, string $slug, ?Page $from = null, bool $template = false, ?int $userId = null): Page
    {
        return DB::transaction(function () use ($title, $slug, $from, $template, $userId) {
            $page = Page::create([
                'title' => $title,
                'slug' => $slug,
                'is_active' => false,
                'is_template' => $template,
                'description' => $template ? $from?->description : null,
                'settings' => $from ? array_diff_key((array) $from->settings, array_flip(['meta_title', 'meta_description', 'og_image'])) : [],
                'created_by' => $userId,
            ]);
            if ($from) {
                foreach ($from->blocks()->get() as $b) {
                    $page->blocks()->create($b->only(['type', 'anchor', 'data', 'is_active', 'sort']));
                }
            }

            return $page;
        });
    }

    /** قالب‌های آماده‌ی config/page_templates.php را (اگر هنوز نیستند) می‌سازد. */
    public static function seedTemplates(): void
    {
        foreach ((array) config('page_templates', []) as $key => $tpl) {
            $slug = 'template-'.$key;
            if (Page::where('slug', $slug)->exists()) {
                continue;
            }
            $page = Page::create([
                'title' => $tpl['title'],
                'slug' => $slug,
                'is_active' => false,
                'is_template' => true,
                'description' => $tpl['description'] ?? null,
                'settings' => [],
            ]);
            foreach ($tpl['blocks'] as $item) {
                [$type, $overrides] = is_array($item) ? [$item[0], $item[1] ?? []] : [$item, []];
                if (config()->has('blocks.'.$type)) {
                    self::addBlock($page, $type, $overrides);
                }
            }
        }
    }

    /**
     * آیا این آدرس برای صفحه آزاد است؟ مسیرهای فعلی سایت (/blog، /panel، ...) و پوشه/فایل‌های public
     * همیشه بر صفحه‌ساز مقدم‌اند، پس صفحه با این آدرس هرگز دیده نمی‌شد.
     */
    public static function slugIsReserved(string $slug): bool
    {
        if (in_array($slug, ['index.php', 'storage', 'build', 'vendor'], true) || file_exists(public_path($slug))) {
            return true;
        }
        try {
            $route = Route::getRoutes()->match(Request::create('/'.$slug, 'GET'));

            return ! $route->isFallback;
        } catch (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return true; // مسیری با متد دیگر (مثلاً فقط POST) روی همین آدرس هست
        } catch (\Throwable) {
            return false; // هیچ مسیری مطابقت نداشت
        }
    }

    /** پیشنهاد آدرس از عنوان (لاتین یا فارسی) که تکراری و رزروشده نباشد. */
    public static function suggestSlug(string $title, ?int $ignoreId = null): string
    {
        $base = self::normalizeSlug($title) ?: 'page';
        $slug = $base;
        $i = 2;
        while (self::slugIsReserved($slug) || Page::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public static function normalizeSlug(string $v): string
    {
        $v = mb_strtolower(trim($v));
        $v = preg_replace('/[^a-z0-9\x{0600}-\x{06FF}\x{200C}]+/u', '-', $v) ?? '';
        $v = str_replace("\u{200C}", '-', $v);

        return trim(preg_replace('/-{2,}/', '-', $v) ?? '', '-');
    }
}
