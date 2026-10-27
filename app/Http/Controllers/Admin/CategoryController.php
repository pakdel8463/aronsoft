<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type') === 'portfolio' ? 'portfolio' : 'blog';

        $all = Category::ofType($type)
            ->withCount(['posts', 'portfolios'])
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.dash.categories.index', [
            'type' => $type,
            'tree' => $this->flatten($all),
            'total' => $all->count(),
            'typeCounts' => ['blog' => Category::ofType('blog')->count(), 'portfolio' => Category::ofType('portfolio')->count()],
        ]);
    }

    public function create(Request $request)
    {
        $category = new Category(['type' => $request->query('type') === 'portfolio' ? 'portfolio' : 'blog', 'is_active' => true, 'parent_id' => $request->query('parent')]);

        return view('admin.dash.categories.form', ['category' => $category, 'parents' => $this->parentOptions($category)]);
    }

    public function edit(Category $category)
    {
        return view('admin.dash.categories.form', ['category' => $category, 'parents' => $this->parentOptions($category)]);
    }

    public function show(Category $category)
    {
        return redirect()->route('panel.categories.edit', $category);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        $data['sort_order'] = (int) Category::where('parent_id', $data['parent_id'])->max('sort_order') + 1;
        $category = Category::create($data);

        return redirect()->route('panel.categories.index', ['type' => $category->kind])->with('success', 'دسته‌بندی ایجاد شد.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);
        if ((int) $data['parent_id'] !== (int) $category->parent_id) {
            $data['sort_order'] = (int) Category::where('parent_id', $data['parent_id'])->max('sort_order') + 1;
        }
        $category->update($data);
        // نوع فرزندان همیشه با والد یکی است
        if ($category->wasChanged('type')) {
            Category::whereIn('id', $category->descendantIds())->update(['type' => $category->type]);
        }

        return redirect()->route('panel.categories.index', ['type' => $category->kind])->with('success', 'دسته‌بندی ذخیره شد.');
    }

    /** ایجاد سریع (از ادیتور نوشته) */
    public function ajaxStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'type' => 'nullable|in:blog,portfolio',
        ]);
        $parent = ! empty($data['parent_id']) ? Category::find($data['parent_id']) : null;
        $type = $parent?->type ?: ($data['type'] ?? 'blog');

        $category = Category::create([
            'name' => trim($data['name']),
            'slug' => Category::generateUniqueSlug($data['name']),
            'parent_id' => $parent?->id,
            'type' => $type,
            'is_active' => true,
            'language' => 'fa',
            'sort_order' => (int) Category::where('parent_id', $parent?->id)->max('sort_order') + 1,
        ]);

        return response()->json(['status' => 'ok', 'id' => $category->id, 'name' => $category->name, 'parent_id' => $category->parent_id, 'depth' => $parent ? 1 : 0]);
    }

    /** ذخیره‌ی ترتیب هم‌نیاها: ids[] به ترتیب جدید */
    public function reorder(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer|exists:categories,id']);
        foreach (array_values($data['ids']) as $i => $id) {
            Category::where('id', $id)->update(['sort_order' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }

    public function toggle(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        return response()->json(['ok' => true, 'is_active' => (bool) $category->is_active]);
    }

    /**
     * حذف امن: نوشته‌ها/نمونه‌کارها یا به دسته‌ی انتخابی منتقل می‌شوند (reassign_to) یا — اگر دسته‌ی دیگری نداشته باشند —
     * به «بدون دسته‌بندی» می‌روند. فرزندان به والد دسته‌ی حذف‌شده وصل می‌شوند.
     */
    public function destroy(Request $request, Category $category)
    {
        if ($category->slug === Category::FALLBACK_SLUG && $category->posts()->exists()) {
            return back()->with('error', 'دسته‌ی «بدون دسته‌بندی» تا وقتی نوشته دارد حذف نمی‌شود.');
        }

        $target = null;
        if ($request->filled('reassign_to')) {
            $target = Category::find((int) $request->input('reassign_to'));
            if (! $target || $target->id === $category->id || $target->kind !== $category->kind || in_array($target->id, $category->descendantIds(), true)) {
                return back()->with('error', 'دسته‌ی مقصد معتبر نیست.');
            }
        }

        \DB::transaction(function () use ($category, $target) {
            $postIds = $category->posts()->pluck('posts.id')->all();
            $portfolioIds = $category->portfolios()->pluck('portfolios.id')->all();
            $category->posts()->detach();
            $category->portfolios()->detach();

            if ($category->kind === 'blog' && $postIds) {
                $dest = $target ?: null;
                foreach (Post::withTrashed()->whereIn('id', $postIds)->get() as $post) {
                    if ($dest) {
                        $post->categories()->syncWithoutDetaching([$dest->id]);
                    } elseif (! $post->categories()->exists()) {
                        $post->categories()->syncWithoutDetaching([Category::fallback()->id]);
                    }
                }
            } elseif ($category->kind === 'portfolio' && $target && $portfolioIds) {
                $target->portfolios()->syncWithoutDetaching($portfolioIds);
            }

            Category::where('parent_id', $category->id)->update(['parent_id' => $category->parent_id]);
            $category->forceDelete(); // حذف کامل تا اسلاگ آزاد شود
        });

        return redirect()->route('panel.categories.index', ['type' => $category->kind])->with('success', 'دسته‌بندی حذف شد.');
    }

    /* ------------------------------------------------------------------ utils */

    private function validated(Request $request, ?Category $category): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:190',
            'type' => 'nullable|in:blog,portfolio',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $parent = ! empty($data['parent_id']) ? Category::find($data['parent_id']) : null;
        if ($parent && $category && ($parent->id === $category->id || in_array($parent->id, $category->descendantIds(), true))) {
            throw ValidationException::withMessages(['parent_id' => 'یک دسته نمی‌تواند زیرمجموعه‌ی خودش یا فرزندانش باشد.']);
        }

        $slugIn = trim((string) ($data['slug'] ?? ''));
        $slug = ($category && $slugIn === $category->slug)
            ? $category->slug
            : Category::generateUniqueSlug($slugIn !== '' ? $slugIn : $data['name'], $category?->id);

        return [
            'name' => trim($data['name']),
            'slug' => $slug,
            'type' => $parent?->type ?: ($data['type'] ?? 'blog'),
            'parent_id' => $parent?->id,
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'language' => $category->language ?? 'fa',
        ];
    }

    /** گزینه‌های والد: همه‌ی دسته‌ها به‌جز خودش و نوادگانش (به‌صورت درخت تو رفته) */
    private function parentOptions(Category $category): array
    {
        $exclude = $category->exists ? array_merge([$category->id], $category->descendantIds()) : [];
        $all = Category::whereNotIn('id', $exclude ?: [0])->orderBy('sort_order')->orderBy('id')->get();

        return $this->flatten($all, true);
    }

    /** @return array<int,array<string,mixed>> */
    private function flatten($all, bool $simple = false): array
    {
        $by = $all->groupBy(fn ($c) => $c->parent_id ?: 0);
        $ids = $all->pluck('id')->all();
        $out = [];
        $walk = function ($pid, $depth, $siblings) use (&$walk, $by, &$out, $simple) {
            $list = $by->get($pid, collect())->values();
            foreach ($list as $i => $c) {
                $out[] = ['model' => $c, 'depth' => $depth, 'first' => $i === 0, 'last' => $i === $list->count() - 1, 'siblings' => $list->count()];
                $walk($c->id, $depth + 1, $list);
            }
        };
        $walk(0, 0, collect());
        // دسته‌هایی که والدشان در این مجموعه نیست، ریشه حساب می‌شوند
        foreach ($all as $c) {
            if ($c->parent_id && ! in_array($c->parent_id, $ids, true) && ! collect($out)->contains(fn ($r) => $r['model']->id === $c->id)) {
                $out[] = ['model' => $c, 'depth' => 0, 'first' => true, 'last' => true, 'siblings' => 1];
            }
        }

        return $out;
    }
}
