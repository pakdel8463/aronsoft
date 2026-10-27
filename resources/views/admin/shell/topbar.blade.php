@php($authUser = auth()->user())
<header class="sticky top-0 z-40 border-b border-line/80 bg-white/85 backdrop-blur-xl">
    <div class="flex h-16 items-center gap-2 px-3 sm:gap-3 sm:px-5 xl:px-8">
        <button type="button" data-adm-drawer-open class="grid size-10 shrink-0 place-items-center rounded-2xl border border-line bg-white text-slate-600 hover:bg-aron-50 lg:hidden" aria-label="باز کردن منو"><i class="ph ph-list text-xl"></i></button>
        <button type="button" data-adm-sidebar-toggle class="hidden size-10 shrink-0 place-items-center rounded-2xl border border-line bg-white text-slate-500 hover:bg-aron-50 hover:text-aron-700 lg:grid" aria-label="جمع‌کردن منو"><i class="ph ph-sidebar-simple text-xl"></i></button>

        <div class="relative min-w-0 max-w-md flex-1" data-adm-search-wrap>
            <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
            <input type="search" data-adm-search autocomplete="off" placeholder="جستجو در بخش‌های پنل…  ( / )"
                   class="h-10 w-full rounded-2xl border border-transparent bg-canvas pe-3 ps-10 text-sm outline-none transition placeholder:text-slate-400 focus:border-aron-300 focus:bg-white focus:ring-4 focus:ring-aron-100">
            <div data-adm-search-results class="hidden absolute inset-x-0 top-12 z-50 max-h-80 overflow-y-auto rounded-2xl border border-line bg-white p-2 shadow-adm-pop"></div>
        </div>

        <div class="ms-auto flex shrink-0 items-center gap-2">
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="a-btn a-btn-secondary a-btn-sm hidden sm:inline-flex"><i class="ph ph-arrow-square-out"></i> مشاهده سایت</a>

            @if($authUser?->isSuperAdmin())
                @php($unread = $authUser->unreadNotifications()->count())
                <div class="relative" data-dropdown>
                    <button type="button" data-dropdown-trigger aria-expanded="false" aria-label="اعلان‌ها" class="relative grid size-10 place-items-center rounded-2xl border border-line bg-white text-slate-600"><i class="ph ph-bell text-xl"></i>@if($unread)<span class="absolute -top-1 -left-1 rounded-full bg-red-500 px-1.5 text-xs text-white">{{ $unread }}</span>@endif</button>
                    <div data-dropdown-panel class="hidden absolute end-0 top-12 z-50 w-80 max-w-[85vw] rounded-2xl border border-line bg-white p-3 shadow-adm-pop">
                        <strong class="mb-2 block text-sm">اعلان‌های جدید</strong>
                        @forelse($authUser->unreadNotifications()->latest()->limit(6)->get() as $notice)
                            <div class="border-b p-2 text-sm"><div class="font-semibold">{{ $notice->data['heading'] ?? 'اعلان' }}</div><div class="mt-2 flex gap-3"><a class="text-blue-600" href="{{ $notice->data['url'] ?? route('panel.notifications.index') }}">مشاهده</a><form method="POST" action="{{ route('panel.notifications.read',$notice->id) }}">@csrf<button class="text-slate-500">خواندم</button></form></div></div>
                        @empty <p class="p-3 text-sm text-slate-400">اعلان خوانده‌نشده‌ای وجود ندارد.</p> @endforelse
                        <a class="mt-2 block text-center text-sm text-blue-600" href="{{ route('panel.notifications.index') }}">تاریخچه ارسال</a>
                    </div>
                </div>
            @endif

            <div class="relative" data-dropdown>
                <button type="button" data-dropdown-trigger aria-expanded="false" class="flex items-center gap-2 rounded-2xl border border-line bg-white py-1 pe-3 ps-1 hover:bg-aron-50">
                    <span class="grid size-8 place-items-center rounded-xl bg-aron-600 text-sm font-extrabold text-white">{{ \Illuminate\Support\Str::of($authUser?->name ?? 'م')->substr(0, 1) }}</span>
                    <span class="hidden max-w-28 truncate text-sm font-bold sm:block">{{ $authUser?->name }}</span>
                    <i class="ph ph-caret-down hidden text-xs text-slate-400 sm:block"></i>
                </button>
                <div data-dropdown-panel class="hidden absolute end-0 top-12 z-50 w-64 max-w-[85vw] rounded-2xl border border-line bg-white p-2 shadow-adm-pop">
                    <div class="mb-1 border-b border-line px-3 pb-3 pt-2">
                        <strong class="block truncate text-sm font-extrabold">{{ $authUser?->name }}</strong>
                        <small class="block truncate text-xs text-slate-400" dir="ltr">{{ $authUser?->email }}</small>
                    </div>
                    @if (\Illuminate\Support\Facades\Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-aron-50 hover:text-aron-700"><i class="ph ph-user-circle text-lg"></i> پروفایل من</a>
                    @endif
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-aron-50 hover:text-aron-700"><i class="ph ph-globe text-lg"></i> مشاهده سایت</a>
                    @if (\Illuminate\Support\Facades\Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-line pt-1">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-coral-600 hover:bg-coral-50"><i class="ph ph-sign-out text-lg"></i> خروج</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
