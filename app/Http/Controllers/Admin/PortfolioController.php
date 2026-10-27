<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PortfolioRequest;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\PortfolioImage;
use App\Models\Tag;
use App\Models\TeamMember;
use App\Support\JalaliInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    /** پوشه‌ی تصویر شاخص که پنل خودش مدیریت (و هنگام حذف پاک) می‌کند. */
    private const FEATURED_DIR = 'portfolio/featured';

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $status = $request->query('status');

        $portfolios = Portfolio::query()
            ->withCount('images')
            ->with('categories:id,name')
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $w->where('title', 'like', $like)->orWhere('client_name', 'like', $like)->orWhere('slug', 'like', $like);
            }))
            ->when(array_key_exists((string) $status, Portfolio::STATUSES), fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.dash.portfolio.index', compact('portfolios', 'q', 'status'));
    }

    public function create()
    {
        return view('admin.dash.portfolio.create', $this->formData(new Portfolio(['status' => 'draft', 'progress' => 100])));
    }

    public function store(PortfolioRequest $request)
    {
        $portfolio = DB::transaction(function () use ($request) {
            $portfolio = Portfolio::create($request->columns());
            $this->syncRelations($portfolio, $request);

            return $portfolio;
        });

        return redirect()->route('panel.portfolios.edit', $portfolio)->with('success', 'نمونه‌کار با موفقیت ذخیره شد.');
    }

    public function show(Portfolio $portfolio)
    {
        return redirect()->route('panel.portfolios.edit', $portfolio);
    }

    public function edit(Portfolio $portfolio)
    {
        $portfolio->load(['categories:id', 'tags:id,name', 'teamMembers:id', 'images']);

        return view('admin.dash.portfolio.edit', $this->formData($portfolio));
    }

    public function update(PortfolioRequest $request, Portfolio $portfolio)
    {
        $oldFeatured = $portfolio->featured_image;

        DB::transaction(function () use ($request, $portfolio) {
            $portfolio->update($request->columns($portfolio));
            $this->syncRelations($portfolio, $request);
        });

        if ($oldFeatured && $oldFeatured !== $portfolio->featured_image) {
            $this->deleteManagedFile($oldFeatured);
        }

        return redirect()->route('panel.portfolios.index')->with('success', 'نمونه‌کار با موفقیت بروزرسانی شد.');
    }

    public function destroy(Portfolio $portfolio)
    {
        DB::transaction(function () use ($portfolio) {
            foreach ($portfolio->tags as $tag) {
                if ($tag->count > 0) {
                    $tag->decrement('count');
                }
            }
            $portfolio->tags()->detach();
            $portfolio->categories()->detach();
            $portfolio->teamMembers()->detach();
            $portfolio->images->each->delete(); // فایل‌ها در رویداد deleting مدل پاک می‌شوند
            $portfolio->delete();
        });
        $this->deleteManagedFile($portfolio->featured_image);

        return redirect()->route('panel.portfolios.index')->with('success', 'نمونه‌کار حذف شد.');
    }

    /**
     * آپلود تصویر (آژاکس).
     * - پیش‌فرض: تصاویر گالری؛ رکورد بدون portfolio_id ساخته و هنگام ذخیره‌ی فرم متصل می‌شود.
     * - purpose=featured: فقط فایل ذخیره و مسیر برگردانده می‌شود (تصویر شاخص).
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:20'],
            'images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'purpose'  => ['nullable', 'in:gallery,featured'],
        ], [
            'images.required' => 'تصویری ارسال نشده است.',
            'images.max'      => 'در هر بار حداکثر ۲۰ تصویر قابل آپلود است.',
            'images.*.image'  => 'فایل انتخاب‌شده تصویر معتبر نیست.',
            'images.*.mimes'  => 'فقط فرمت‌های jpg، png و webp مجاز است.',
            'images.*.max'    => 'حجم هر تصویر حداکثر ۴ مگابایت است.',
        ]);

        $featured = $request->input('purpose') === 'featured';
        $this->purgeOrphans();

        $files = [];
        foreach ($request->file('images') as $file) {
            $name = Str::random(24).'.'.($file->extension() ?: 'jpg');

            if ($featured) {
                $file->storeAs(self::FEATURED_DIR, $name, 'public');
                $files[] = ['id' => null, 'file' => $name, 'path' => 'storage/'.self::FEATURED_DIR.'/'.$name, 'url' => asset('storage/'.self::FEATURED_DIR.'/'.$name)];
                continue;
            }

            $file->storeAs(PortfolioImage::DIR, $name, 'public');
            $image = PortfolioImage::create(['portfolio_id' => null, 'image' => $name, 'sort' => 0]);
            $files[] = ['id' => $image->id, 'file' => $name, 'path' => $image->path, 'url' => $image->url];
        }

        return response()->json(['status' => 'success', 'files' => $files]);
    }

    /** ساخت سریع عضو تیم از داخل فرم نمونه‌کار. */
    public function storeTeamMember(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:120'],
        ], [], ['name' => 'نام', 'role' => 'نقش']);

        $member = TeamMember::firstOrCreate(['name' => $data['name']], ['role' => $data['role'] ?? null]);

        return response()->json(['status' => 'success', 'member' => ['id' => $member->id, 'name' => $member->name, 'role' => $member->role]]);
    }

    // ---------------------------------------------------------------- helpers

    private function formData(Portfolio $portfolio): array
    {
        return [
            'portfolio'          => $portfolio,
            'categories'         => Category::where('type', 'portfolio')->orderBy('name')->get(['id', 'name']),
            'teamMembers'        => TeamMember::orderBy('name')->get(),
            'tagSuggestions'     => Tag::orderByDesc('count')->limit(60)->pluck('name'),
            'selectedCategories' => $portfolio->exists ? $portfolio->categories->pluck('id')->all() : [],
            'selectedTeam'       => $portfolio->exists ? $portfolio->teamMembers->pluck('id')->all() : [],
            'jalali'             => fn ($d) => JalaliInput::format($d),
        ];
    }

    private function syncRelations(Portfolio $portfolio, PortfolioRequest $request): void
    {
        $portfolio->categories()->sync($request->input('categories', []));
        $portfolio->teamMembers()->sync($request->input('team_members', []));
        $this->syncTags($portfolio, (array) $request->input('tags', []));

        if ($request->boolean('gallery_submitted')) {
            $this->syncGallery($portfolio, array_map('intval', (array) $request->input('gallery', [])));
        }
    }

    /** تگ‌ها: ساخت در صورت نبودن، و شمارنده فقط برای تگ‌های واقعاً اضافه/حذف‌شده تغییر می‌کند. */
    private function syncTags(Portfolio $portfolio, array $names): void
    {
        $ids = [];
        foreach (array_unique(array_filter(array_map('trim', $names), 'strlen')) as $name) {
            $tag = Tag::where('name', $name)->first()
                ?? Tag::where('slug', Str::slug($name))->first()
                ?? Tag::create(['name' => $name, 'slug' => Tag::generateUniqueSlug($name), 'language' => 'fa', 'count' => 0]);
            $ids[] = $tag->id;
        }

        $changes = $portfolio->tags()->sync($ids);
        if ($changes['attached']) {
            Tag::whereIn('id', $changes['attached'])->increment('count');
        }
        if ($changes['detached']) {
            Tag::whereIn('id', $changes['detached'])->where('count', '>', 0)->decrement('count');
        }
    }

    /** ترتیب و اتصال تصاویر گالری؛ تصاویری که در فهرست نیستند (فایل هم) حذف می‌شوند. */
    private function syncGallery(Portfolio $portfolio, array $ids): void
    {
        $valid = PortfolioImage::whereIn('id', $ids)
            ->where(fn ($q) => $q->whereNull('portfolio_id')->orWhere('portfolio_id', $portfolio->id))
            ->pluck('id')->all();
        $ordered = array_values(array_filter($ids, fn ($id) => in_array($id, $valid, true)));

        PortfolioImage::where('portfolio_id', $portfolio->id)->whereNotIn('id', $ordered)->get()->each->delete();
        foreach ($ordered as $i => $id) {
            PortfolioImage::where('id', $id)->update(['portfolio_id' => $portfolio->id, 'sort' => $i + 1]);
        }
    }

    /** تصاویر آپلودشده‌ای که بیش از ۲۴ ساعت به هیچ نمونه‌کاری وصل نشده‌اند. */
    private function purgeOrphans(): void
    {
        PortfolioImage::whereNull('portfolio_id')->where('created_at', '<', now()->subDay())->get()->each->delete();
    }

    /** فقط فایل‌هایی که خود پنل ساخته پاک می‌شوند (نه تصاویر دمو/کتابخانه‌ی رسانه). */
    private function deleteManagedFile(?string $path): void
    {
        $prefix = 'storage/'.self::FEATURED_DIR.'/';
        if ($path && str_starts_with($path, $prefix) && ! str_contains($path, '..')) {
            Storage::disk('public')->delete(self::FEATURED_DIR.'/'.basename($path));
        }
    }
}
