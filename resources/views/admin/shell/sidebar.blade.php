{{--
    Admin navigation definition + permission filtering. Included by the sidebar.
    To add a page: add a row  [label, phosphor-icon, route-name, active-pattern, permissions[]|null]
    Rows whose route does not exist (Route::has) are skipped automatically.
--}}
@php
    $user = auth()->user();

    $can = function (?array $perms) use ($user) {
        if (empty($perms)) return true;
        if (! $user) return false;
        foreach ($perms as $p) {
            try { if ($user->hasPermission($p)) return true; } catch (\Throwable $e) { /* no permission tables */ }
        }
        return false;
    };

    // cheap, cached badge counters
    $counts = \Illuminate\Support\Facades\Cache::remember('admin.nav.counts', 30, function () {
        $c = ['leads' => 0, 'messages' => 0, 'comments' => 0, 'tickets' => 0];
        try { $c['leads'] = \App\Models\Lead::where('is_contacted', false)->count(); } catch (\Throwable $e) {}
        try { $c['messages'] = \App\Models\ContactMessage::where('status', 'new')->count(); } catch (\Throwable $e) {}
        try { $c['comments'] = \App\Models\Comment::where('status', 'pending')->count(); } catch (\Throwable $e) {}
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('tickets') && \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'status')) {
                $c['tickets'] = \Illuminate\Support\Facades\DB::table('tickets')->whereIn('status', ['open', 'new', 'pending', 'waiting_admin'])->count();
            }
        } catch (\Throwable $e) {}
        return $c;
    });

    // [label, icon, route, active pattern, permissions (any-of) | null, badge key | null]
    $groups = [
        ['title' => null, 'items' => [
            ['داشبورد', 'squares-four', 'panel.home', 'panel.home', null, null],
        ]],
        ['title' => 'محتوا', 'items' => [
            ['نوشته‌ها', 'article', 'panel.posts.index', 'panel.posts.*', null, null],
            ['دسته‌بندی‌ها', 'folders', 'panel.categories.index', 'panel.categories.*', null, null],
            ['برچسب‌ها', 'tag', 'panel.tags.index', 'panel.tags.*', null, null],
            ['دیدگاه‌ها', 'chat-centered-text', 'panel.comments.index', 'panel.comments.*', ['comments-index'], 'comments'],
            ['نمونه‌کارها', 'briefcase', 'panel.portfolios.index', 'panel.portfolios.*', null, null],
            ['صفحات لندینگ', 'browser', 'panel.landing.index', 'panel.landing.*', null, null],
            ['لندینگ محصولات', 'rocket-launch', 'panel.product-pages.index', 'panel.product-pages.*', ['product-pages.manage'], null],
            ['منوها', 'list-bullets', 'panel.menus.index', 'panel.menus.*', ['menus-index'], null],
            ['رسانه', 'images', 'panel.media.index', 'panel.media.*', null, null],
            ['مدیریت فایل‌ها', 'folder-open', 'unisharp.lfm.show', 'unisharp.lfm.*', null, null],
        ]],
        ['title' => 'فروش و ارتباط', 'items' => [
            ['لیدها', 'target', 'panel.leads.index', 'panel.leads.*', ['leads-index'], 'leads'],
            ['پیام‌های تماس', 'envelope-simple', 'panel.contact-messages.index', 'panel.contact-messages.*', null, 'messages'],
            ['تیکت‌ها', 'lifebuoy', 'panel.tickets.index', 'panel.tickets.*', ['tickets.manage'], 'tickets'],
            ['سفارش‌ها', 'shopping-bag', 'panel.orders.index', 'panel.orders.*', null, null],
            ['محصولات', 'package', 'panel.products.index', 'panel.products.*', ['products-index'], null],
            ['پیش‌فاکتورها', 'receipt', 'panel.proformas.index', 'panel.proformas.*', null, null],
            ['خبرنامه', 'megaphone', 'panel.newsletter.index', 'panel.newsletter.*', null, null],
        ]],
        ['title' => 'مدیریت', 'items' => [
            ['کاربران', 'users', 'panel.users.index', 'panel.users.*', ['users.index'], null],
            ['نقش‌ها', 'shield-check', 'panel.roles.index', 'panel.roles.*', ['role-index'], null],
            ['دسترسی‌ها', 'key', 'panel.permissions.index', 'panel.permissions.*', ['permission-index', 'permissions-index'], null],
            ['تنظیمات تماس', 'address-book', 'panel.settings.contact', 'panel.settings.contact*', ['settings.contact'], null],
        ]],
    ];

    $navGroups = collect($groups)->map(function ($g) use ($can, $counts) {
        $g['items'] = collect($g['items'])
            ->filter(fn ($i) => \Illuminate\Support\Facades\Route::has($i[2]) && $can($i[4]))
            ->map(fn ($i) => [
                'label' => $i[0], 'icon' => $i[1], 'url' => route($i[2]),
                'active' => request()->routeIs($i[3]), 'badge' => $i[5] ? (int) ($counts[$i[5]] ?? 0) : 0,
            ])->values();
        return $g;
    })->filter(fn ($g) => $g['items']->isNotEmpty())->values();
@endphp
<aside class="adm-sidebar" id="adm-sidebar" aria-label="منوی اصلی">
    <div class="flex items-center justify-between gap-2 px-4 pb-3 pt-5">
        <a href="{{ route('panel.home') }}" class="flex min-w-0 items-center gap-3">
            <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-aron-600 text-lg font-black text-white shadow-adm-brand">A</span>
            <span class="adm-sb-brand-copy min-w-0">
                <strong class="block truncate text-[15px] font-extrabold leading-5">آرون سافت</strong>
                <small class="block truncate text-[11px] font-medium text-white/45">پنل مدیریت</small>
            </span>
        </a>
        <button type="button" data-adm-drawer-close class="grid size-9 place-items-center rounded-xl text-white/60 hover:bg-white/10 lg:hidden" aria-label="بستن منو"><i class="ph ph-x text-lg"></i></button>
    </div>

    <nav class="adm-sb-nav min-h-0 flex-1 space-y-5 overflow-y-auto px-3 pb-4 pt-2">
        @foreach ($navGroups as $group)
            <div>
                @if ($group['title'])
                    <div class="adm-sb-heading mb-1.5 flex items-center px-3">
                        <span class="adm-sb-heading-text text-[11px] font-bold tracking-wide text-white/35">{{ $group['title'] }}</span>
                    </div>
                @endif
                <ul class="space-y-1">
                    @foreach ($group['items'] as $item)
                        <li>
                            <a href="{{ $item['url'] }}" class="adm-sb-link" title="{{ $item['label'] }}"
                               data-adm-nav-link data-label="{{ $item['label'] }}" data-group="{{ $group['title'] }}" data-icon="{{ $item['icon'] }}"
                               @if ($item['active']) aria-current="page" @endif>
                                <i class="ph{{ $item['active'] ? '-fill' : '' }} ph-{{ $item['icon'] }}"></i>
                                <span class="adm-sb-label min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                @if ($item['badge'] > 0)
                                    <span class="adm-sb-badge grid min-w-5 place-items-center rounded-full bg-coral-500 px-1.5 text-[10px] font-extrabold leading-5 text-white">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-3">
        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="adm-sb-link" title="مشاهده سایت">
            <i class="ph ph-arrow-square-out"></i><span class="adm-sb-label">مشاهده سایت</span>
        </a>
        <button type="button" data-adm-sidebar-toggle class="adm-sb-link mt-1 hidden w-full lg:flex" title="جمع‌کردن منو">
            <i class="ph ph-sidebar-simple"></i><span class="adm-sb-label">جمع‌کردن منو</span>
        </button>
    </div>
</aside>
