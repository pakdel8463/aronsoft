<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $sort = in_array($request->query('sort'), ['name', 'count', 'new'], true) ? $request->query('sort') : 'name';
        $q = trim((string) $request->query('q', ''));

        $tags = Tag::query()
            ->withCount('posts')
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%")))
            ->when($sort === 'name', fn ($w) => $w->orderBy('name'))
            ->when($sort === 'count', fn ($w) => $w->orderByDesc('posts_count')->orderBy('name'))
            ->when($sort === 'new', fn ($w) => $w->orderByDesc('id'))
            ->paginate(30)->withQueryString();

        return view('admin.dash.tags.index', ['tags' => $tags, 'q' => $q, 'sort' => $sort, 'total' => Tag::count()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80', 'slug' => 'nullable|string|max:190', 'description' => 'nullable|string|max:1000']);
        $name = trim($data['name']);
        if (Tag::where('name', $name)->exists()) {
            return back()->withInput()->withErrors(['name' => 'این برچسب از قبل وجود دارد.']);
        }
        $tag = Tag::create([
            'name' => $name,
            'slug' => Tag::generateUniqueSlug(($data['slug'] ?? '') !== '' ? $data['slug'] : $name),
            'description' => $data['description'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'id' => $tag->id, 'name' => $tag->name]);
        }

        return back()->with('success', 'برچسب ایجاد شد.');
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate(['name' => 'required|string|max:80', 'slug' => 'nullable|string|max:190', 'description' => 'nullable|string|max:1000']);
        $name = trim($data['name']);
        if (Tag::where('name', $name)->where('id', '!=', $tag->id)->exists()) {
            return back()->withInput()->withErrors(['name' => 'برچسب دیگری با این نام وجود دارد.']);
        }
        $slugIn = trim((string) ($data['slug'] ?? ''));
        $tag->update([
            'name' => $name,
            'slug' => ($slugIn === '' || $slugIn === $tag->slug) ? $tag->slug : Tag::generateUniqueSlug($slugIn, $tag->id),
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('success', 'برچسب ذخیره شد.');
    }

    public function destroy(Tag $tag)
    {
        $tag->posts()->detach();
        $tag->portfolios()->detach();
        $tag->forceDelete();

        return back()->with('success', 'برچسب حذف شد.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $n = 0;
        foreach (Tag::whereIn('id', $data['ids'])->get() as $tag) {
            $tag->posts()->detach();
            $tag->portfolios()->detach();
            $tag->forceDelete();
            $n++;
        }

        return back()->with('success', "{$n} برچسب حذف شد.");
    }

    /** JSON: پیشنهاد نام برچسب هنگام تایپ */
    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        return response()->json(
            Tag::query()
                ->when($q !== '', fn ($w) => $w->where('name', 'like', "%{$q}%"))
                ->orderByDesc('count')->orderBy('name')->limit(12)->pluck('name')
        );
    }
}
