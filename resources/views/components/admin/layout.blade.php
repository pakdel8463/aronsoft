@props([
    'title' => 'پنل مدیریت',
    'subtitle' => null,
    'breadcrumbs' => [],
    'header' => true,
    'width' => 'max-w-[1600px]',
])
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#2F4FF5">
    <title>{{ $title }} | پنل مدیریت</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('vendor/phosphor/phosphor-admin.css') }}">
    @php
        $admManifest = public_path('build/manifest.json');
        $admBuilt = file_exists(public_path('hot')) || (is_file($admManifest) && str_contains((string) file_get_contents($admManifest), '"resources/css/admin.css"'));
    @endphp
    @if ($admBuilt)
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @endif
    {{ $head ?? '' }}
</head>
<body>
@unless ($admBuilt)
    <div style="background:#ff6b5b;color:#fff;padding:10px 16px;font:600 14px sans-serif;text-align:center">
        فایل‌های استایل پنل ساخته نشده‌اند — دستور <code dir="ltr">npm install && npm run build</code> را اجرا کنید.
    </div>
@endunless
<div class="adm-shell">
    @include('admin.shell.sidebar')
    <div class="adm-overlay" data-adm-overlay></div>

    <div class="adm-main">
        @include('admin.shell.topbar')

        <main class="mx-auto w-full {{ $width }} p-3 pb-16 sm:p-5 xl:p-8">
            @if ($header)
                <x-admin.page-header :title="$title" :subtitle="$subtitle" :breadcrumbs="$breadcrumbs">
                    @isset($actions)
                        <x-slot:actions>{{ $actions }}</x-slot:actions>
                    @endisset
                </x-admin.page-header>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@include('admin.shell.flash')
{{ $scripts ?? '' }}
</body>
</html>
