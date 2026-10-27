<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>document.documentElement.classList.add('js');</script>
    @php
        // اگر صفحه‌ای عنوان/توضیح ثبت نکرده باشد، مقادیر پیش‌فرض سایت (config/seotools.php) استفاده می‌شود.
        $__fv = fn ($p) => file_exists(public_path($p)) ? '?v='.filemtime(public_path($p)) : '';
    @endphp
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/android-icon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-icon-180x180.png') }}">
    <link rel="manifest" href="{{ asset('images/manifest.json') }}">
    <link rel="preload" href="{{ asset('fonts/vazirmatn/Vazirmatn%5Bwght%5D.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('vendor/phosphor/phosphor.css') }}{{ $__fv('vendor/phosphor/phosphor.css') }}">
    @vite(['resources/css/front.css', 'resources/js/front.js'])
    @yield('topcss')
    @yield('head')
    @include('front.t-one.partials.gtm')
</head>
<body class="min-h-screen bg-white font-sans text-slate-800 antialiased">

@if(config('services.google_tag_manager.enabled', app()->environment('production')))
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google_tag_manager.id') ?: 'GTM-MMJ4NTXW' }}" height="0" width="0" style="display:none;visibility:hidden" title="GTM"></iframe></noscript>
@endif

<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:right-4 focus:top-4 focus:z-[200] focus:rounded-xl focus:bg-white focus:px-4 focus:py-3 focus:text-sm focus:font-bold focus:text-tech-blue focus:shadow-card">پرش به محتوای اصلی</a>

@include('front.t-one.layouts.navbar')

<main id="main-content">
    @yield('main')
</main>

@include('front.t-one.layouts.footer')
@include('front.t-one.partials.estimator')

<x-consent-banner />

@yield('bottomscript')
@yield('botscript')
</body>
</html>
