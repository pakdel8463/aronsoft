<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LandingPageController as PublicLandingController;
use App\Http\Requests\Admin\LandingPageRequest;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $landings = LandingPage::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like));
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // صفحات عمومی که در کد (blade) تعریف شده‌اند؛ برای راهنمایی ویرایشگر
        $codePages = collect(PublicLandingController::publicSlugs())
            ->map(fn ($slug) => ['slug' => $slug, 'url' => route('landing.show', $slug)])
            ->all();

        return view('admin.dash.landing.index', [
            'landings'  => $landings,
            'q'         => $q,
            'codePages' => $codePages,
            'templates' => LandingPage::templates() + ['cms' => 'لندینگ محتوایی (ویرایشگر)'],
            'portfolios' => Portfolio::query()->orderBy('title')->get(['id','title']),
        ]);
    }

    public function create()
    {
        return view('admin.dash.landing.create', [
            'landing'   => new LandingPage(['is_active' => true]),
            'templates' => LandingPage::templates() + ['cms' => 'لندینگ محتوایی (ویرایشگر)'],
            'portfolios' => Portfolio::query()->orderBy('title')->get(['id','title']),
        ]);
    }

    public function store(LandingPageRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['created_by'] = auth()->id();
        $data['sections'] = []; $data['draft_sections'] = $this->parseSections($request); unset($data['sections_json']);

        $landing = LandingPage::create($data);

        return redirect()->route('panel.landing.edit', $landing)->with('success', 'لندینگ با موفقیت ایجاد شد.');
    }

    public function show(LandingPage $landing)
    {
        return redirect()->route('panel.landing.edit', $landing);
    }

    public function edit(LandingPage $landing)
    {
        return view('admin.dash.landing.edit', [
            'landing'   => $landing,
            'templates' => LandingPage::templates() + ['cms' => 'لندینگ محتوایی (ویرایشگر)'],
            'portfolios' => Portfolio::query()->orderBy('title')->get(['id','title']),
        ]);
    }

    public function update(LandingPageRequest $request, LandingPage $landing)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        if ($landing->template === 'cms' || ($data['template'] ?? '') === 'cms') { $data['draft_sections'] = $this->parseSections($request); }
        unset($data['sections_json']);
        $landing->update($data);

        return redirect()->route('panel.landing.index')->with('success', 'لندینگ با موفقیت بروزرسانی شد.');
    }

    private function parseSections(Request $request): array
    {
        $sections = json_decode($request->input('sections_json', '[]'), true);
        if (! is_array($sections) || ! array_is_list($sections) || count($sections) > 40) abort(422, 'ساختار سکشن‌ها معتبر نیست.');
        $allowed = ['hero','features','gallery','text','stats','faq','cta','portfolio'];
        foreach ($sections as &$section) {
            if (! is_array($section) || ! in_array($section['type'] ?? '', $allowed, true)) abort(422, 'نوع سکشن نامعتبر است.');
            $section = array_intersect_key($section, array_flip(['type','title','subtitle','body','image','items','button_text','button_url','enabled']));
            foreach (['title','subtitle','body','image','button_text','button_url'] as $field) if (isset($section[$field])) $section[$field] = mb_substr((string) $section[$field], 0, $field==='body' ? 10000 : 1000);
            foreach (['button_url','image'] as $urlField) { if (!empty($section[$urlField]) && !preg_match('~^(https?://|/|#)~i', $section[$urlField])) $section[$urlField] = ''; }
            $section['enabled'] = (bool)($section['enabled'] ?? true);
            if (isset($section['items'])) { if (!is_array($section['items'])) $section['items']=[]; $section['items']=array_slice(array_map(fn($item)=> mb_substr((string)$item,0,600),$section['items']),0,24); }
        }
        return $sections;
    }

    public function upload(Request $request)
    {
        $file = $request->validate(['image'=>['required','image','mimes:jpg,jpeg,png,webp','max:4096']]);
        $path = $request->file('image')->store('landings', 'public');
        return response()->json(['url'=>Storage::disk('public')->url($path)]);
    }

    public function publish(LandingPage $landing)
    {
        abort_unless($landing->template === 'cms', 422);
        $landing->update(['sections'=>$landing->draft_sections ?? [], 'published_at'=>now(), 'is_active'=>true]);
        return back()->with('success','نسخه جدید منتشر شد.');
    }

    public function preview(LandingPage $landing)
    {
        abort_unless($landing->template === 'cms', 404);
        return view('front.t-one.landings.cms', ['landing'=>$landing, 'sections'=>$landing->draft_sections ?? [], 'preview'=>true]);
    }

    public function destroy(LandingPage $landing)
    {
        $landing->delete();

        return redirect()->route('panel.landing.index')->with('success', 'لندینگ حذف شد.');
    }
}
