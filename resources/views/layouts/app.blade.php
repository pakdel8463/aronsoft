{{-- لایه‌ی حساب کاربری (x-app-layout): داشبورد و پروفایل روی همان قالب سایت --}}
@php
    $__inner = $slot;
    $__header = $header ?? null;
    \App\View\Seo::meta(['title' => trim(strip_tags((string) $__header)) ?: 'حساب کاربری', 'description' => 'حساب کاربری آرون سافت', 'noindex' => true]);
@endphp
@component('front.t-one.layouts.content')
    <div class="border-b border-slate-100 bg-gradient-to-b from-brand-50/60 to-white">
        <div class="container-x py-8 md:py-10">
            <nav class="mb-4 flex flex-wrap gap-2" aria-label="حساب کاربری">
                <a href="{{ route('dashboard') }}" class="chip-filter {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>داشبورد</a>
                <a href="{{ route('profile.edit') }}" class="chip-filter {{ request()->routeIs('profile.*') ? 'is-active' : '' }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif>پروفایل</a>
                <a href="{{ route('tickets.index') }}" class="chip-filter {{ request()->routeIs('tickets.*') ? 'is-active' : '' }}">تیکت‌ها</a>
            </nav>
            <h1 class="h1-page">{{ $__header }}</h1>
        </div>
    </div>
    <div class="section"><div class="container-x max-w-4xl">{!! $__inner !!}</div></div>
@endcomponent
