@php
    $initial = $menuItems->map(fn ($i) => [
        'title' => $i->display_title, 'url' => $i->url ?? '', 'depth' => (int) $i->depth,
        'model_type' => $i->model ?: null, 'model_id' => $i->model_id,
    ])->values();
    $sources = [
        ['صفحه‌ها (لندینگ)', 'browser', $landingPages->map(fn ($x) => [$x->title, url('services/'.$x->slug), \App\Models\LandingPage::class, $x->id]), true],
        ['نوشته‌ها', 'article', $posts->map(fn ($x) => [$x->title, url('blog/'.$x->slug), \App\Models\Post::class, $x->id]), false],
        ['دسته‌بندی وبلاگ', 'folders', $categories->map(fn ($x) => [$x->name, url('blog/category/'.$x->slug), \App\Models\Category::class, $x->id]), false],
        ['نمونه‌کارها', 'briefcase', $portfolios->map(fn ($x) => [$x->title, url('portfolio/'.$x->slug), \App\Models\Portfolio::class, $x->id]), false],
        ['دسته‌بندی نمونه‌کار', 'squares-four', $portCategories->map(fn ($x) => [$x->name, url('portfolio/category/'.$x->slug), \App\Models\Category::class, $x->id]), false],
    ];
    $quick = [['صفحه اصلی', '/'], ['وبلاگ', '/blog'], ['نمونه‌کارها', '/portfolios'], ['فروشگاه', '/shop'], ['تماس با ما', '/contact'], ['ثبت سفارش', '/order'], ['درباره ما', '/about-us']];
@endphp
<x-admin.layout :title="'آیتم‌های منو: '.$menu->name" :subtitle="$menu->position_label" :breadcrumbs="[['label' => 'منوها', 'url' => route('panel.menus.index')], ['label' => $menu->name]]">
    <x-slot:actions>
        <select class="a-select w-52" data-menu-switch data-current="{{ route('panel.menus.items', $menu) }}" aria-label="انتخاب منو">
            @foreach ($allMenus as $m)<option value="{{ route('panel.menus.items', $m) }}" @selected($m->id === $menu->id)>{{ $m->name }}</option>@endforeach
        </select>
        <x-admin.button :href="route('panel.menus.edit', $menu)" variant="secondary" icon="gear">تنظیمات منو</x-admin.button>
    </x-slot:actions>

    <div data-menu-builder data-save-url="{{ route('panel.menus.save', $menu) }}" class="grid gap-6 lg:grid-cols-3">
        <script type="application/json" data-initial>@json($initial)</script>

        <div class="min-w-0 space-y-3 lg:order-2">
            <h2 class="px-1 text-sm font-extrabold text-slate-600">افزودن به منو</h2>
            @foreach ($sources as [$label, $icon, $rows, $open])
                <details class="a-card group overflow-hidden" @if ($open) open @endif data-source>
                    <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-3.5 text-sm font-extrabold text-ink">
                        <span class="grid size-8 place-items-center rounded-xl bg-aron-50 text-aron-600"><i class="ph ph-{{ $icon }}"></i></span>{{ $label }}
                        <span class="ms-auto text-xs font-bold text-slate-400">{{ $rows->count() }}</span><i class="ph ph-caret-down text-slate-400 transition group-open:rotate-180"></i>
                    </summary>
                    <div class="border-t border-line p-3">
                        @if ($rows->isEmpty())<p class="py-3 text-center text-xs text-slate-400">موردی وجود ندارد.</p>@else
                            @if ($rows->count() > 6)<input type="search" data-source-filter placeholder="فیلتر…" class="a-input mb-2 !h-9 text-xs">@endif
                            <ul class="max-h-56 space-y-0.5 overflow-y-auto">
                                @foreach ($rows as [$t, $u, $m, $id])
                                    <li data-src-item><label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 text-sm hover:bg-aron-50/60">
                                        <input type="checkbox" class="a-check !size-4" data-title="{{ $t }}" data-url="{{ $u }}" data-model="{{ $m }}" data-id="{{ $id }}"><span class="truncate">{{ $t }}</span></label></li>
                                @endforeach
                            </ul>
                            <button type="button" data-add-selected class="a-btn a-btn-soft a-btn-sm mt-3 w-full"><i class="ph ph-plus"></i>افزودن به منو</button>
                        @endif
                    </div>
                </details>
            @endforeach
            <details class="a-card group overflow-hidden" data-source>
                <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-3.5 text-sm font-extrabold text-ink">
                    <span class="grid size-8 place-items-center rounded-xl bg-aron-50 text-aron-600"><i class="ph ph-link"></i></span>پیوند دلخواه
                    <i class="ph ph-caret-down ms-auto text-slate-400 transition group-open:rotate-180"></i>
                </summary>
                <div class="space-y-3 border-t border-line p-3">
                    <div class="flex flex-wrap gap-1.5">@foreach ($quick as [$qt, $qu])<button type="button" data-quick="{{ $qu }}" data-title="{{ $qt }}" class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 hover:bg-aron-50 hover:text-aron-700">+ {{ $qt }}</button>@endforeach</div>
                    <div><label class="a-label">متن پیوند</label><input class="a-input" data-custom-title placeholder="مثلاً: وبلاگ"></div>
                    <div><label class="a-label">نشانی</label><input class="a-input" dir="ltr" data-custom-url placeholder="https://… یا /مسیر"></div>
                    <button type="button" data-add-custom class="a-btn a-btn-soft a-btn-sm w-full"><i class="ph ph-plus"></i>افزودن</button>
                </div>
            </details>
        </div>

        <div class="min-w-0 lg:order-1 lg:col-span-2">
            <section class="a-card overflow-hidden">
                <header class="sticky top-0 z-10 flex flex-wrap items-center justify-between gap-3 border-b border-line bg-white/95 px-5 py-4 backdrop-blur">
                    <div><h2 class="text-base font-extrabold text-ink">ساختار منو <span class="text-xs font-bold text-slate-400">(<span data-count>0</span> آیتم)</span></h2>
                        <p class="text-xs text-slate-500">دستگیره را بکشید یا از دکمه‌های بالا/پایین و زیرمجموعه استفاده کنید. حداکثر ۳ سطح.</p></div>
                    <div class="flex items-center gap-3"><span data-state class="hidden text-xs font-bold text-amber-600">…</span>
                        <button type="button" data-save class="a-btn a-btn-primary"><i class="ph ph-floppy-disk"></i>ذخیره منو</button></div>
                </header>
                <div class="p-4 sm:p-5">
                    <ul data-tree class="space-y-2"></ul>
                    <div data-tree-empty class="hidden"><x-admin.empty-state icon="tree-structure" title="منو خالی است" description="از ستون کناری صفحه‌ها، نوشته‌ها یا پیوندهای دلخواه را اضافه کنید." /></div>
                </div>
            </section>
        </div>
    </div>
</x-admin.layout>
