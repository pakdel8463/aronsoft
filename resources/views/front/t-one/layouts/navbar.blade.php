@php
    $topMenu = isset($globalMenus['top']) ? $globalMenus['top']->tree() : collect();
    $authUser = auth()->user();
    $isStaff = $authUser && ($authUser->is_staff || $authUser->is_superuser);
@endphp

{{-- OVERLAY (موبایل) --}}
<div id="menu-overlay" class="fixed inset-0 z-[60] hidden bg-slate-900/50 opacity-0 transition-opacity duration-300 lg:hidden" aria-hidden="true"></div>

<header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-md" style="height: var(--header-h)">
    <div class="container-x flex h-full items-center justify-between gap-3">

        {{-- لوگو + همبرگر --}}
        <div class="flex items-center gap-1.5">
            <button type="button" data-menu-toggle onclick="toggleMenu()" aria-controls="mobile-menu" aria-expanded="false" aria-label="باز کردن منو"
                    class="btn btn-ghost -me-1 h-11 w-11 min-w-[44px] !p-0 lg:hidden"><i class="ph ph-list text-2xl" aria-hidden="true"></i></button>
            <a href="{{ url('/') }}" class="flex min-h-[44px] items-center gap-2 text-xl font-extrabold tracking-tight" aria-label="آرون سافت — صفحه اصلی">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tech-blue text-lg font-bold text-white" aria-hidden="true">A</span>
                <span class="whitespace-nowrap"><span class="text-slate-900">آرون</span><span class="text-tech-blue">‌سافت</span></span>
            </a>
        </div>

        {{-- منوی دسکتاپ --}}
        <nav class="hidden items-center gap-0.5 lg:flex" aria-label="منوی اصلی">
            @foreach($topMenu as $item)
                <x-nav-menu-item :item="$item" />
            @endforeach
        </nav>

        {{-- اکشن‌ها --}}
        <div class="flex items-center gap-2">
            <button type="button" data-estimator-open onclick="toggleEstimator(true)" aria-controls="estimator-panel" aria-expanded="false"
                    class="btn btn-ghost hidden !px-3 xl:inline-flex" title="محاسبه آنلاین هزینه پروژه">
                <i class="ph ph-calculator text-lg" aria-hidden="true"></i><span>محاسبه قیمت</span>
            </button>

            {{-- زبان --}}
            <details class="menu-details relative hidden sm:block">
                <summary class="btn btn-ghost !px-2.5 list-none [&::-webkit-details-marker]:hidden" aria-label="زبان سایت: فارسی">
                    <i class="ph ph-translate text-lg" aria-hidden="true"></i><span class="text-xs font-bold">FA</span><i class="ph ph-caret-down text-[10px]" aria-hidden="true"></i>
                </summary>
                <ul class="absolute end-0 top-full z-50 mt-2 w-40 rounded-xl border border-slate-200 bg-white p-1.5 shadow-card">
                    <li><span class="flex min-h-[44px] items-center justify-between rounded-lg bg-brand-50 px-3 text-sm font-bold text-tech-blue" aria-current="true">فارسی <i class="ph ph-check" aria-hidden="true"></i></span></li>
                    <li><span class="flex min-h-[44px] items-center justify-between rounded-lg px-3 text-sm text-slate-400" aria-disabled="true">English <span class="text-[11px]">به‌زودی</span></span></li>
                </ul>
            </details>

            @guest
                <a href="{{ route('login') }}" class="btn btn-ghost hidden md:inline-flex">ورود / ثبت‌نام</a>
                <a href="{{ url('/contact') }}" class="btn btn-primary btn-sm sm:!min-h-[44px] sm:px-5">درخواست مشاوره</a>
            @endguest

            @auth
                <details class="menu-details relative">
                    <summary class="btn btn-ghost list-none gap-2 !px-2 [&::-webkit-details-marker]:hidden" aria-label="منوی حساب کاربری">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-sm font-extrabold text-tech-blue" aria-hidden="true">{{ mb_substr($authUser->name, 0, 1) }}</span>
                        <span class="hidden max-w-[7rem] truncate text-sm sm:inline">{{ $authUser->name }}</span>
                        <i class="ph ph-caret-down text-[10px]" aria-hidden="true"></i>
                    </summary>
                    <div class="absolute end-0 top-full z-50 mt-2 w-52 rounded-xl border border-slate-200 bg-white p-1.5 shadow-card">
                        <a href="{{ route('dashboard') }}" class="flex min-h-[44px] items-center rounded-lg px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">داشبورد</a>
                        <a href="{{ route('profile.edit') }}" class="flex min-h-[44px] items-center rounded-lg px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">ویرایش پروفایل</a>
                        <a href="{{ route('tickets.index') }}" class="flex min-h-[44px] items-center rounded-lg px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">تیکت‌های پشتیبانی</a>
                        @if($isStaff)
                            <a href="{{ route('panel.home') }}" class="flex min-h-[44px] items-center rounded-lg px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">پنل مدیریت</a>
                        @endif
                        <div class="my-1 border-t border-slate-100"></div>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="flex min-h-[44px] w-full items-center rounded-lg px-3 text-sm font-medium text-red-600 hover:bg-red-50">خروج</button>
                        </form>
                    </div>
                </details>
            @endauth
        </div>
    </div>
</header>

{{-- منوی کشویی موبایل --}}
<aside id="mobile-menu" aria-hidden="true" aria-label="منوی موبایل" inert
       class="fixed inset-y-0 right-0 z-[70] flex h-full w-[min(20rem,88vw)] translate-x-full flex-col border-s border-slate-200 bg-white shadow-2xl transition-transform duration-300 lg:hidden">
    <div class="flex h-[var(--header-h)] shrink-0 items-center justify-between border-b border-slate-100 px-4">
        <a href="{{ url('/') }}" class="flex min-h-[44px] items-center gap-2 text-xl font-extrabold tracking-tight">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tech-blue text-lg font-bold text-white" aria-hidden="true">A</span>
            <span><span class="text-slate-900">آرون</span><span class="text-tech-blue">‌سافت</span></span>
        </a>
        <button type="button" onclick="toggleMenu()" class="btn btn-ghost h-11 w-11 min-w-[44px] !p-0" aria-label="بستن منو"><i class="ph ph-x text-2xl" aria-hidden="true"></i></button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="منوی موبایل">
        @foreach($topMenu as $item)
            <a href="{{ $item->href ?: '#' }}" class="flex min-h-[48px] items-center rounded-xl px-4 text-[0.9375rem] font-bold text-slate-800 hover:bg-slate-50">{{ $item->display_title }}</a>
            @if($item->children->count())
                <div class="mb-1 me-4 space-y-0.5 border-e-2 border-slate-100 pe-3">
                    @foreach($item->children as $child)
                        <a href="{{ $child->href ?: '#' }}" class="flex min-h-[44px] items-center rounded-lg px-3 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-tech-blue">{{ $child->display_title }}</a>
                    @endforeach
                </div>
            @endif
        @endforeach
        <button type="button" onclick="toggleMenu(); toggleEstimator(true)" class="flex min-h-[48px] w-full items-center gap-2 rounded-xl px-4 text-start text-[0.9375rem] font-bold text-tech-blue hover:bg-brand-50"><i class="ph ph-calculator text-lg" aria-hidden="true"></i> محاسبه قیمت پروژه</button>
    </nav>

    <div class="shrink-0 space-y-2 border-t border-slate-100 p-4 pb-safe">
        <a href="{{ url('/order') }}" class="btn btn-primary btn-block">ثبت سفارش پروژه</a>
        @guest
            <a href="{{ route('login') }}" class="btn btn-secondary btn-block">ورود / ثبت‌نام</a>
        @else
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-block">داشبورد من</a>
        @endguest
        <p class="pt-1 text-center text-xs text-slate-500">زبان: <span class="font-bold text-slate-700">فارسی</span> · English به‌زودی</p>
    </div>
</aside>
