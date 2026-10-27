@php
    \App\View\Seo::meta([
        'title' => trim($__env->yieldContent('title', 'ورود به حساب')),
        'description' => 'ورود و ثبت‌نام در حساب کاربری آرون سافت',
        'canonical' => url()->current(),
        'noindex' => true,
    ]);
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>document.documentElement.classList.add('js');</script>
    {!! \Artesaos\SEOTools\Facades\SEOMeta::generate() !!}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="preload" href="{{ asset('fonts/vazirmatn/Vazirmatn%5Bwght%5D.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('vendor/phosphor/phosphor.css') }}">
    @vite(['resources/css/front.css', 'resources/js/front.js'])
</head>
<body class="min-h-screen bg-white font-sans text-slate-800 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">
    <main id="main-content" class="flex flex-col px-5 py-6 sm:px-10 lg:px-16">
        <a href="{{ url('/') }}" class="inline-flex min-h-[44px] items-center gap-2 self-start text-xl font-extrabold tracking-tight" aria-label="آرون سافت — بازگشت به صفحه اصلی">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-tech-blue text-white" aria-hidden="true">A</span><span>آرون<span class="text-tech-blue">سافت</span></span>
        </a>
        <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-10">
            <h1 class="text-2xl font-extrabold text-slate-900 sm:text-[1.75rem]">@yield('heading')</h1>
            @hasSection('subheading')<p class="mt-2 text-sm leading-7 text-slate-600">@yield('subheading')</p>@endif
            <div class="mt-7">@yield('auth')</div>
            @hasSection('alt')<p class="mt-7 text-center text-sm text-slate-600">@yield('alt')</p>@endif
        </div>
    </main>

    <aside class="relative hidden overflow-hidden bg-slate-50 lg:flex lg:items-center lg:justify-center" aria-hidden="true">
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 text-slate-300/60"></div>
        <div class="hero-glow pointer-events-none absolute inset-0"></div>
        <div class="relative w-full max-w-sm px-8">
            <div class="mock-window" dir="ltr">
                <div class="mock-bar"><span></span><span></span><span></span><b class="ms-3 text-xs font-medium text-slate-400">dashboard</b></div>
                <div class="space-y-3 p-5">
                    <div class="flex items-center gap-3"><span class="icon-tile-sm"><i class="ph ph-rocket-launch"></i></span><div><p class="text-sm font-bold text-slate-800">New project</p><p class="text-xs text-slate-500">Order → Proforma → Delivery</p></div></div>
                    @foreach([['Discovery', '100%', 'w-full'], ['Design', '75%', 'w-3/4'], ['Development', '40%', 'w-2/5']] as [$l, $p, $w])
                        <div><div class="mb-1 flex justify-between text-xs text-slate-500"><span>{{ $l }}</span><span>{{ $p }}</span></div><div class="h-1.5 rounded-full bg-slate-200"><div class="h-full rounded-full bg-tech-blue {{ $w }}"></div></div></div>
                    @endforeach
                </div>
            </div>
            <p class="mt-6 text-center text-sm leading-7 text-slate-600">سفارش‌ها، پیش‌فاکتورها و پیگیری پروژه‌ها در یک حساب کاربری.</p>
        </div>
    </aside>
</div>
@yield('botscript')
</body>
</html>
