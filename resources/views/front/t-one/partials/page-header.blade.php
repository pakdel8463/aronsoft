{{-- $crumbs [[label,url|null]...] | $title | $lead (اختیاری) | $eyebrow (اختیاری) --}}
<header class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-brand-50/60 to-white">
    <div class="bg-dot-pattern pointer-events-none absolute inset-0 text-slate-300/50" aria-hidden="true"></div>
    <div class="container-x relative py-10 md:py-14">
        @include('front.t-one.partials.breadcrumb', ['items' => $crumbs])
        <div class="max-w-3xl">
            @isset($eyebrow)<span class="badge mb-3">{{ $eyebrow }}</span>@endisset
            <h1 class="h1-page">{{ $title }}</h1>
            @isset($lead)<p class="lead mt-3">{{ $lead }}</p>@endisset
        </div>
        {{ $slot ?? '' }}
    </div>
</header>
