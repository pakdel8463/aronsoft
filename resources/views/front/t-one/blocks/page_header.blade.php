@php $h = $s['title'] ?: $page->title; @endphp
<header id="{{ $anchor }}" class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-brand-50/60 to-white">
    <div class="bg-dot-pattern pointer-events-none absolute inset-0 text-slate-300/50" aria-hidden="true"></div>
    <div class="container-x relative py-10 md:py-14">
        @if ($s['breadcrumb'])
            @include('front.t-one.partials.breadcrumb', ['items' => [['خانه', route('home')], [$h, null]]])
        @endif
        <div class="max-w-3xl">
            @if ($s['eyebrow'] !== '')<span class="badge mb-3">{{ $s['eyebrow'] }}</span>@endif
            @if ($first ?? false)
                <h1 class="h1-page">{{ $h }}</h1>
            @else
                <h2 class="h1-page">{{ $h }}</h2>
            @endif
            @if ($s['lead'] !== '')<p class="lead mt-3">{{ $s['lead'] }}</p>@endif
        </div>
    </div>
</header>
