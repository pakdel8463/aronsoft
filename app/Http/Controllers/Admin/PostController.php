<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\MediaRelation;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Tag;
use App\Models\User;
use App\Services\Media\MediaException;
use App\Services\Media\MediaUploader;
use App\Support\HtmlSanitizer;
use App\Support\Slugger;
use App\Support\TextStats;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class PostController extends Controller
{
    private const SORTS = [
        'new' => ['label' => 'جدیدترین', 'col' => 'created_at', 'dir' => 'desc'],
        'old' => ['label' => 'قدیمی‌ترین', 'col' => 'created_at', 'dir' => 'asc'],
        'updated' => ['label' => 'آخرین ویرایش', 'col' => 'updated_at', 'dir' => 'desc'],
        'published' => ['label' => 'تاریخ انتشار', 'col' => 'published_at', 'dir' => 'desc'],
        'title' => ['label' => 'عنوان (الفبا)', 'col' => 'title', 'dir' => 'asc'],
        'comments' => ['label' => 'بیشترین دیدگاه', 'col' => 'comments_count', 'dir' => 'desc'],
    ];

    /* ------------------------------------------------------------------ list */

    public function index(Request $request)
    {
        $tab = $request->query('status', 'all');
        if (! in_array($tab, ['all', 'published', 'draft', 'scheduled', 'private', 'trash'], true)) {
            $tab = 'all';
        }
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'new';

        $query = Post::query()
            ->with(['author:id,name', 'categories:id,name,type', 'tags:id,name'])
            ->withCount('comments');

        match ($tab) {
            'trash' => $query->onlyTrashed(),
            'published' => $query->published(),
            'scheduled' => $query->scheduled(),
            'draft' => $query->where('status', 'draft'),
            'private' => $query->where('status', 'private'),
            default => null,
        };

        if (($q = trim((string) $request->query('q', $request->query('search', '')))) !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
            $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('body', 'like', $like));
        }
        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn ($w) => $w->where('categories.id', (int) $request->query('category_id')));
        }
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', fn ($w) => $w->where('tags.id', (int) $request->query('tag_id')));
        }
        if ($request->filled('author_id')) {
            $query->where('author_id', (int) $request->query('author_id'));
        }
        if ($d = $this->parseDate($request->query('from'))) {
            $query->where('created_at', '>=', $d->copy()->startOfDay());
        }
        if ($d = $this->parseDate($request->query('to'))) {
            $query->where('created_at', '<=', $d->copy()->endOfDay());
        }

        $s = self::SORTS[$sort];
        $query->orderBy($s['col'], $s['dir'])->orderByDesc('id');

        $posts = $query->paginate(15)->withQueryString();

        $now = now();
        $counts = [
            'all' => Post::count(),
            'published' => Post::published()->count(),
            'draft' => Post::where('status', 'draft')->count(),
            'scheduled' => Post::scheduled()->count(),
            'private' => Post::where('status', 'private')->count(),
            'trash' => Post::onlyTrashed()->count(),
        ];

        return view('admin.dash.posts.index', [
            'posts' => $posts,
            'counts' => $counts,
            'tab' => $tab,
            'sort' => $sort,
            'sorts' => collect(self::SORTS)->map(fn ($x) => $x['label'])->all(),
            'categoryOptions' => $this->categoryOptions('blog'),
            'authors' => User::whereIn('id', Post::withTrashed()->select('author_id'))->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** JSON: جستجوی سریع نوشته (برای انتخابگرها) */
    public function liveSearch(Request $request)
    {
        $q = trim((string) $request->query('search', $request->query('q', '')));
        $posts = Post::query()
            ->when($q !== '', fn ($w) => $w->where('title', 'like', '%'.$q.'%'))
            ->latest('id')->limit(20)->get(['id', 'title', 'slug', 'status']);

        return response()->json($posts->map(fn ($p) => ['id' => $p->id, 'title' => $p->title, 'slug' => $p->slug, 'status' => $p->status, 'url' => route('blog.show', $p->slug)]));
    }

    /* ----------------------------------------------------------- create/edit */

    public function create()
    {
        $post = new Post(['status' => 'draft', 'commentable' => true]);

        return view('admin.dash.posts.form', $this->formData($post));
    }

    public function edit(string $post)
    {
        $post = $this->resolve($post);

        return view('admin.dash.posts.form', $this->formData($post));
    }

    private function formData(Post $post): array
    {
        $post->loadMissing(['categories:id', 'tags:id,name']);

        return [
            'post' => $post,
            'categoryOptions' => $this->categoryOptions('blog'),
            'selectedCategories' => $post->exists ? $post->categories->pluck('id')->all() : [],
            'tagNames' => $post->exists ? $post->tags->pluck('name')->all() : [],
            'allTags' => Tag::orderByDesc('count')->orderBy('name')->limit(60)->pluck('name')->all(),
            'revisions' => $post->exists ? $post->revisions()->with('user:id,name')->limit(10)->get() : collect(),
        ];
    }

    /* ---------------------------------------------------------------- store */

    public function store(Request $request)
    {
        $post = new Post;
        $post->author_id = auth()->id();
        $this->persist($request, $post);

        return $this->afterSave($request, $post, 'نوشته ایجاد شد.');
    }

    public function update(Request $request, string $post)
    {
        $post = $this->resolve($post);
        $this->persist($request, $post);

        return $this->afterSave($request, $post, 'نوشته ذخیره شد.');
    }

    private function afterSave(Request $request, Post $post, string $msg)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'id' => $post->id, 'message' => $msg, 'edit_url' => route('panel.posts.edit', $post->id)]);
        }
        if ($request->has('_continue')) {
            return redirect()->route('panel.posts.edit', $post->id)->with('success', $msg);
        }

        return redirect()->route('panel.posts.index')->with('success', $msg);
    }

    private function rules(?Post $post): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:190',
            'body' => 'nullable|string',
            'excerpt' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published,scheduled,private',
            'published_at' => 'nullable|string|max:40',
            'featured_image' => 'nullable|string|max:500',
            'alt' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'tags' => 'nullable',
            'tags.*' => 'nullable|string|max:80',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'og_image' => 'nullable|string|max:500',
            'password' => 'nullable|string|max:100',
        ];
    }

    /** ذخیره‌ی مشترک ایجاد/ویرایش */
    private function persist(Request $request, Post $post): void
    {
        $data = $request->validate($this->rules($post->exists ? $post : null));

        $when = $this->parseDate($data['published_at'] ?? null, true);
        if (filled($data['published_at'] ?? null) && ! $when) {
            throw ValidationException::withMessages(['published_at' => 'تاریخ انتشار معتبر نیست.']);
        }

        $status = $data['status'];
        if ($status === 'scheduled') {
            if (! $when || $when->lte(now())) {
                throw ValidationException::withMessages(['published_at' => 'برای زمان‌بندی، تاریخ و ساعتی در آینده انتخاب کنید.']);
            }
            $status = 'published';
        } elseif ($status === 'published' && ! $when) {
            $when = $post->published_at && $post->exists && $post->status === 'published' ? $post->published_at : now();
        }

        $body = HtmlSanitizer::clean($data['body'] ?? '');

        // slug: دستی (یکتاسازی) یا از عنوان
        $slugInput = trim((string) ($data['slug'] ?? ''));
        if ($post->exists && $slugInput === $post->slug) {
            $slug = $post->slug;
        } else {
            $slug = Post::generateUniqueSlug($slugInput !== '' ? $slugInput : $data['title'], $post->id);
        }

        // نسخه‌ی قبلی (revision-lite)
        if ($post->exists && ($post->title !== $data['title'] || (string) $post->body !== $body)) {
            PostRevision::create(['post_id' => $post->id, 'user_id' => auth()->id(), 'title' => $post->title, 'body' => $post->body, 'created_at' => now()]);
            $keep = PostRevision::where('post_id', $post->id)->orderByDesc('id')->limit(15)->pluck('id');
            PostRevision::where('post_id', $post->id)->whereNotIn('id', $keep)->delete();
        }

        $post->fill([
            'title' => trim($data['title']),
            'slug' => $slug,
            'body' => $body,
            'excerpt' => $this->nullIfBlank($data['excerpt'] ?? null),
            'status' => $status,
            'published_at' => $when,
            'featured_image' => $this->nullIfBlank($data['featured_image'] ?? null),
            'alt' => $this->nullIfBlank($data['alt'] ?? null),
            'meta_title' => $this->nullIfBlank($data['meta_title'] ?? null),
            'meta_description' => $this->nullIfBlank($data['meta_description'] ?? null),
            'canonical_url' => $this->nullIfBlank($data['canonical_url'] ?? null),
            'og_image' => $this->nullIfBlank($data['og_image'] ?? null),
            'noindex' => $request->boolean('noindex'),
            'commentable' => $request->boolean('commentable'),
            'updated_by' => auth()->id(),
        ]);

        // رمز فقط برای نوشته‌ی خصوصی معنا دارد؛ خالی گذاشتن = بدون تغییر
        if ($status === 'private') {
            if (filled($data['password'] ?? null)) {
                $post->password = Hash::make($data['password']);
            }
            if ($request->boolean('remove_password')) {
                $post->password = null;
            }
        } else {
            $post->password = null;
        }

        $post->seo_score = $this->seoScore($post);
        $post->save();

        $post->categories()->sync($data['categories'] ?? []);
        $this->syncTags($post, $this->tagNames($request->input('tags')));
        $this->syncMediaUsage($post);
    }

    /* -------------------------------------------------------- bulk / actions */

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:publish,draft,trash,restore,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $posts = Post::withTrashed()->whereIn('id', $data['ids'])->get();
        $n = 0;
        foreach ($posts as $p) {
            switch ($data['action']) {
                case 'publish':
                    if ($p->trashed()) {
                        continue 2;
                    }
                    $p->update(['status' => 'published', 'published_at' => $p->published_at ?: now(), 'updated_by' => auth()->id()]);
                    break;
                case 'draft':
                    if ($p->trashed()) {
                        continue 2;
                    }
                    $p->update(['status' => 'draft', 'updated_by' => auth()->id()]);
                    break;
                case 'trash':
                    if (! $p->trashed()) {
                        $p->delete();
                    }
                    break;
                case 'restore':
                    if ($p->trashed()) {
                        $p->restore();
                    }
                    break;
                case 'delete':
                    $this->purge($p);
                    break;
            }
            $n++;
        }

        $label = ['publish' => 'منتشر شد', 'draft' => 'به پیش‌نویس رفت', 'trash' => 'به سطل زباله رفت', 'restore' => 'بازیابی شد', 'delete' => 'برای همیشه حذف شد'][$data['action']];

        return back()->with('success', "{$n} نوشته {$label}.");
    }

    /** ویرایش سریع (AJAX) */
    public function quickUpdate(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:posts,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:190',
            'status' => 'required|in:draft,published,scheduled,private',
            'published_at' => 'nullable|string|max:40',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'tags' => 'nullable',
            'commentable' => 'nullable|boolean',
        ]);

        $post = Post::withTrashed()->findOrFail($data['id']);
        $when = $this->parseDate($data['published_at'] ?? null, true);
        $status = $data['status'];
        if ($status === 'scheduled') {
            if (! $when || $when->lte(now())) {
                throw ValidationException::withMessages(['published_at' => 'برای زمان‌بندی، تاریخ و ساعتی در آینده انتخاب کنید.']);
            }
            $status = 'published';
        } elseif ($status === 'published' && ! $when) {
            $when = $post->published_at ?: now();
        }

        $slugInput = trim((string) ($data['slug'] ?? ''));
        $post->fill([
            'title' => trim($data['title']),
            'slug' => ($slugInput === '' || $slugInput === $post->slug) ? $post->slug : Post::generateUniqueSlug($slugInput, $post->id),
            'status' => $status,
            'published_at' => $when ?: $post->published_at,
            'commentable' => $request->boolean('commentable'),
            'updated_by' => auth()->id(),
        ]);
        if ($status !== 'private') {
            $post->password = null;
        }
        $post->save();
        $post->categories()->sync($data['categories'] ?? []);
        $this->syncTags($post, $this->tagNames($request->input('tags')));

        return response()->json(['ok' => true, 'status' => 'success', 'message' => 'تغییرات ذخیره شد.']);
    }

    public function destroy(string $post)
    {
        $p = $this->resolve($post);
        if ($p->trashed()) {
            $this->purge($p);

            return back()->with('success', 'نوشته برای همیشه حذف شد.');
        }
        $p->delete();

        return back()->with('success', 'نوشته به سطل زباله منتقل شد.');
    }

    public function restore(string $post)
    {
        $p = $this->resolve($post);
        $p->restore();

        return back()->with('success', 'نوشته بازیابی شد.');
    }

    public function forceDelete(string $post)
    {
        $this->purge($this->resolve($post));

        return back()->with('success', 'نوشته برای همیشه حذف شد.');
    }

    public function duplicate(string $post)
    {
        $src = $this->resolve($post);
        $copy = $src->replicate(['password', 'seo_score', 'published_at']);
        $copy->title = 'کپی — '.$src->title;
        $copy->slug = Post::generateUniqueSlug($src->slug.'-copy');
        $copy->status = 'draft';
        $copy->published_at = null;
        $copy->password = null;
        $copy->author_id = auth()->id();
        $copy->updated_by = auth()->id();
        $copy->save();
        $copy->categories()->sync($src->categories()->pluck('categories.id'));
        $copy->tags()->sync($src->tags()->pluck('tags.id'));
        $this->recountTags($src->tags()->pluck('tags.id')->all());

        return redirect()->route('panel.posts.edit', $copy->id)->with('success', 'نسخه‌ی پیش‌نویس ساخته شد.');
    }

    /** پیش‌نمایش: ادمین/کارمند می‌تواند پیش‌نویس را در سایت ببیند */
    public function preview(string $post)
    {
        return redirect()->to($this->resolve($post)->publicUrl());
    }

    public function revision(string $post, int $revision)
    {
        $p = $this->resolve($post);
        $rev = PostRevision::where('post_id', $p->id)->findOrFail($revision);

        return response()->json(['id' => $rev->id, 'title' => $rev->title, 'body' => $rev->body]);
    }

    /** پیشنهاد اسلاگ یکتا (JSON) */
    public function slugCheck(Request $request)
    {
        $ignore = $request->filled('ignore') ? (int) $request->query('ignore') : null;
        $value = (string) ($request->query('slug') ?: $request->query('title'));

        return response()->json([
            'slug' => Post::generateUniqueSlug($value, $ignore),
            'taken' => $request->filled('slug') && Slugger::exists(Post::class, Slugger::make($request->query('slug')), $ignore),
        ]);
    }

    /** سازگاری با فرم‌های قدیمی: آپلود تصویر از طریق کتابخانه رسانه */
    public function upload(Request $request, MediaUploader $uploader)
    {
        $file = $request->file('image') ?: $request->file('file') ?: ($request->file('files')[0] ?? null);
        if (! $file) {
            return response()->json(['status' => 'error', 'message' => 'فایلی ارسال نشد.'], 422);
        }
        try {
            $media = $uploader->store($file, $request->user());
        } catch (MediaException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json(['status' => 'ok', 'path' => $media->rel_url, 'url' => $media->url, 'id' => $media->id]);
    }

    /* ----------------------------------------------------------------- utils */

    /** شناسه یا اسلاگ → نوشته (شامل حذف‌شده‌ها) */
    private function resolve(string $key): Post
    {
        if (ctype_digit($key) && ($p = Post::withTrashed()->find((int) $key))) {
            return $p;
        }

        return Post::withTrashed()->where('slug', $key)->firstOrFail();
    }

    private function purge(Post $p): void
    {
        $tagIds = $p->tags()->pluck('tags.id')->all();
        MediaRelation::where('model_type', $p->getMorphClass())->where('model_id', $p->id)->delete();
        $p->forceDelete();
        $this->recountTags($tagIds);
    }

    /** @return array<int,array{id:int,name:string,depth:int}> */
    private function categoryOptions(string $type): array
    {
        $all = Category::ofType($type)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'parent_id', 'is_active']);
        $by = $all->groupBy(fn ($c) => $c->parent_id ?: 0);
        $out = [];
        $walk = function ($pid, $depth) use (&$walk, $by, &$out) {
            foreach ($by->get($pid, collect()) as $c) {
                $out[] = ['id' => $c->id, 'name' => $c->name, 'depth' => $depth, 'active' => (bool) $c->is_active];
                $walk($c->id, $depth + 1);
            }
        };
        $walk(0, 0);
        $seen = array_column($out, 'id');
        foreach ($all as $c) { // یتیم‌ها (والد حذف‌شده/نوع متفاوت)
            if (! in_array($c->id, $seen, true)) {
                $out[] = ['id' => $c->id, 'name' => $c->name, 'depth' => 0, 'active' => (bool) $c->is_active];
            }
        }

        return $out;
    }

    private function nullIfBlank(?string $v): ?string
    {
        $v = $v === null ? null : trim($v);

        return $v === '' ? null : $v;
    }

    /** ورودی تاریخ: Y-m-d[ T]H:i (میلادی)، یا Y/m/d شمسی (با ارقام فارسی هم) */
    private function parseDate(?string $v, bool $withTime = false): ?Carbon
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        $v = CalendarUtils::convertNumbers($v, true);
        try {
            if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})(?:[ T](\d{1,2}):(\d{2}))?/', $v, $m)) {
                $y = (int) $m[1];
                if ($y < 1700) {
                    $c = (new Jalalian($y, (int) $m[2], (int) $m[3], (int) ($m[4] ?? 0), (int) ($m[5] ?? 0), 0))->toCarbon();
                } else {
                    $c = Carbon::create($y, (int) $m[2], (int) $m[3], (int) ($m[4] ?? 0), (int) ($m[5] ?? 0), 0);
                }

                return $c ?: null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /** @return string[] */
    private function tagNames($raw): array
    {
        $list = is_array($raw) ? $raw : explode(',', str_replace('،', ',', (string) $raw));
        $names = [];
        foreach ($list as $n) {
            $n = trim(preg_replace('/\s+/u', ' ', (string) $n));
            $n = ltrim($n, '#');
            if ($n !== '' && mb_strlen($n) <= 80) {
                $names[mb_strtolower($n)] = $n;
            }
        }

        return array_values($names);
    }

    private function syncTags(Post $post, array $names): void
    {
        $before = $post->tags()->pluck('tags.id')->all();
        $ids = [];
        foreach ($names as $name) {
            $tag = Tag::where('name', $name)->first()
                ?: Tag::withTrashed()->where('slug', Slugger::make($name))->first();
            if ($tag && $tag->trashed()) {
                $tag->restore();
            }
            if (! $tag) {
                $tag = Tag::create(['name' => $name, 'slug' => Tag::generateUniqueSlug($name)]);
            }
            $ids[] = $tag->id;
        }
        $post->tags()->sync($ids);
        $this->recountTags(array_unique(array_merge($before, $ids)));
    }

    private function recountTags(array $ids): void
    {
        foreach (Tag::whereIn('id', $ids)->withCount('posts')->get() as $t) {
            if ((int) $t->count !== (int) $t->posts_count) {
                $t->forceFill(['count' => $t->posts_count])->save();
            }
        }
    }

    /** ثبت استفاده‌ی نوشته از رسانه‌ها (تصویر شاخص + تصاویر داخل متن) */
    private function syncMediaUsage(Post $post): void
    {
        try {
            $featured = Media::findByUrl($post->featured_image);
            MediaRelation::syncFor($post, 'featured', $featured ? [$featured->id] : []);

            $ids = [];
            if (preg_match_all('#<img[^>]+src=["\']([^"\']+)["\']#i', (string) $post->body, $m)) {
                foreach (array_slice(array_unique($m[1]), 0, 60) as $src) {
                    if ($media = Media::findByUrl($src)) {
                        $ids[] = $media->id;
                    }
                }
            }
            MediaRelation::syncFor($post, 'content', $ids);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** امتیاز ساده‌ی سئو (۰–۱۰۰) — همان معیارهای نمایش‌داده‌شده در پنل سئو */
    private function seoScore(Post $post): int
    {
        $score = 0;
        $title = $post->meta_title ?: $post->title;
        $len = mb_strlen((string) $title);
        $score += ($len >= 30 && $len <= 65) ? 20 : ($len >= 15 ? 10 : 0);
        $d = mb_strlen((string) ($post->meta_description ?: $post->excerpt));
        $score += ($d >= 70 && $d <= 160) ? 20 : ($d >= 40 ? 10 : 0);
        $words = TextStats::words($post->body);
        $score += $words >= 600 ? 20 : ($words >= 300 ? 14 : ($words >= 100 ? 6 : 0));
        $score += $post->featured_image ? ($post->alt ? 15 : 8) : 0;
        $score += preg_match('#<h[23][\s>]#i', (string) $post->body) ? 10 : 0;
        $score += preg_match('#<a\s[^>]*href#i', (string) $post->body) ? 5 : 0;
        $score += mb_strlen((string) $post->slug) >= 3 && mb_strlen((string) $post->slug) <= 75 ? 5 : 0;
        $score += ! $post->noindex ? 5 : 0;

        return min(100, $score);
    }
}
