<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Portfolio;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller implements HasMiddleware
{
    /** مدل‌هایی که آیتم منو می‌تواند به آن‌ها وصل باشد */
    private const MODELS = [
        Post::class, Portfolio::class, Category::class, LandingPage::class,
    ];

    public static function middleware(): array
    {
        return [new Middleware('can:menus-index')];
    }

    public function index()
    {
        $menus = Menu::withCount('items')->orderBy('id')->paginate(20);

        return view('admin.dash.menus.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.dash.menus.create', ['menu' => new Menu(['position' => 'top']), 'positions' => Menu::POSITIONS]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $menu = Menu::create($data + ['user_id' => auth()->id()]);

        return redirect()->route('panel.menus.items', $menu)->with('success', 'منو ساخته شد. حالا آیتم‌های آن را بچینید.');
    }

    public function edit(Menu $menu)
    {
        return view('admin.dash.menus.edit', ['menu' => $menu->loadCount('items'), 'positions' => Menu::POSITIONS]);
    }

    public function update(Request $request, Menu $menu)
    {
        $menu->update($this->validated($request, $menu));

        return redirect()->route('panel.menus.index')->with('success', 'منو با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete(); // حذف نرم

        return redirect()->route('panel.menus.index')->with('success', 'منو با موفقیت حذف شد.');
    }

    public function items(Menu $menu)
    {
        $posts = Post::select('id', 'title', 'slug')->latest()->take(60)->get();

        return view('admin.dash.menus.items', [
            'menu' => $menu,
            'menuItems' => $menu->items()->orderBy('sort')->get(),
            'landingPages' => LandingPage::select('id', 'title', 'slug')->get(),
            'posts' => $posts,
            'portfolios' => Portfolio::select('id', 'title', 'slug')->get(),
            'categories' => Category::where(fn ($q) => $q->where('type', '!=', 'portfolio')->orWhereNull('type'))->get(['id', 'name', 'slug']),
            'portCategories' => Category::where('type', 'portfolio')->get(['id', 'name', 'slug']),
            'allMenus' => Menu::select('id', 'name', 'position')->orderBy('id')->get(),
        ]);
    }

    /** ذخیره کل درخت منو (لیست تخت + عمق) به‌صورت JSON */
    public function save(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'items' => ['nullable', 'array', 'max:300'],
            'items.*.title' => ['required', 'string', 'max:190'],
            'items.*.url' => ['nullable', 'string', 'max:500'],
            'items.*.depth' => ['nullable', 'integer', 'between:0,2'],
            'items.*.model_type' => ['nullable', 'string', Rule::in(self::MODELS)],
            'items.*.model_id' => ['nullable', 'integer'],
        ], [
            'items.*.title.required' => 'متن همه آیتم‌ها باید پر باشد.',
            'items.*.model_type.in' => 'نوع آیتم نامعتبر است.',
        ]);

        $items = array_values($data['items'] ?? []);

        foreach ($items as $i => $item) {
            if (! $this->safeUrl($item['url'] ?? '')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'نشانی آیتم «'.$item['title'].'» معتبر نیست (فقط http، https، mailto، tel یا مسیر داخلی).',
                    'index' => $i,
                ], 422);
            }
        }

        DB::transaction(function () use ($menu, $items) {
            // آیتم‌های قدیمی (حتی حذف‌نرم‌شده‌ها) پاک می‌شوند تا جدول بی‌دلیل بزرگ نشود
            MenuItem::withTrashed()->where('menu_id', $menu->id)->forceDelete();

            $lastParentIds = [];
            $prevDepth = -1;

            foreach ($items as $index => $item) {
                // عمق نمی‌تواند بیش از یک پله از آیتم قبلی بیشتر باشد
                $depth = min((int) ($item['depth'] ?? 0), $prevDepth + 1);
                $depth = max($depth, 0);
                $parentId = $depth > 0 ? ($lastParentIds[$depth - 1] ?? null) : null;

                $model = ! empty($item['model_type']) && ! empty($item['model_id']) ? $item['model_type'] : null;

                $new = MenuItem::create([
                    'menu_id' => $menu->id,
                    'user_id' => auth()->id(),
                    'display_title' => $item['title'],
                    'url' => $item['url'] ?: '#',
                    'model' => $model,
                    'model_id' => $model ? (int) $item['model_id'] : null,
                    'parent_id' => $parentId,
                    'depth' => $depth,
                    'sort' => $index + 1,
                    'status' => 1,
                    'lang' => 'fa',
                ]);

                $lastParentIds[$depth] = $new->id;
                $prevDepth = $depth;
            }
        });

        return response()->json(['status' => 'success', 'message' => 'منو ذخیره شد.', 'count' => count($items)]);
    }

    private function validated(Request $request, ?Menu $menu = null): array
    {
        $request->merge([
            'position' => $request->input('position') === '_custom' ? trim((string) $request->input('position_custom')) : $request->input('position'),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-]+$/',
                Rule::unique('menus', 'slug')->whereNull('deleted_at')->ignore($menu?->id)],
            // سایت هر موقعیت را فقط با یک منو نمایش می‌دهد (keyBy position)
            'position' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_\-]+$/',
                Rule::unique('menus', 'position')->whereNull('deleted_at')->ignore($menu?->id)],
        ], [
            'slug.regex' => 'نامک فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.',
            'position.regex' => 'موقعیت فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.',
            'position.unique' => 'برای این موقعیت قبلاً یک منو ساخته شده است؛ هر موقعیت فقط یک منو دارد.',
        ]);
    }

    private function safeUrl(?string $url): bool
    {
        $url = trim((string) $url);
        if ($url === '' || $url === '#' || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        return (bool) preg_match('#^(https?://|mailto:|tel:)#i', $url);
    }
}
