@php
    $title = e($s['title']);
    if ($s['title_accent'] !== '' && str_contains($s['title'], $s['title_accent'])) {
        $title = str_replace(e($s['title_accent']), '<span class="text-tech-blue">'.e($s['title_accent']).'</span>', $title);
    }
    $image = \App\Support\ProductPageSchema::imageUrl($s['image']);
    $visual = $s['visual'] === 'image' && ! $image ? 'none' : $s['visual'];
    $centered = $visual === 'none';
@endphp
<section id="{{ $anchor }}" class="relative overflow-hidden" aria-labelledby="{{ $anchor }}-title">
    <div class="hero-glow pointer-events-none absolute inset-0" aria-hidden="true"></div>
    <div class="bg-dot-pattern pointer-events-none absolute inset-0 text-slate-300/60" aria-hidden="true"></div>
    <div class="container-x relative grid items-center gap-10 py-12 md:py-16 lg:gap-8 lg:py-20 {{ $centered ? '' : 'lg:grid-cols-12' }}">
        <div class="{{ $centered ? 'mx-auto max-w-3xl text-center' : 'lg:col-span-6' }}">
            @if ($s['badge'] !== '')
                <span class="badge mb-5"><span class="h-1.5 w-1.5 rounded-full bg-tech-blue" aria-hidden="true"></span> {{ $s['badge'] }}</span>
            @endif
            @if ($first ?? false)
                <h1 id="{{ $anchor }}-title" class="h1-page">{!! $title !!}</h1>
            @else
                <h2 id="{{ $anchor }}-title" class="h1-page">{!! $title !!}</h2>
            @endif
            @if ($s['description'] !== '')<p class="lead mt-4 {{ $centered ? 'mx-auto' : '' }} max-w-xl">{{ $s['description'] }}</p>@endif
            @if ($s['primary_label'] !== '' || $s['secondary_label'] !== '')
                <div class="mt-7 flex flex-col gap-3 sm:flex-row {{ $centered ? 'sm:justify-center' : '' }}">
                    @if ($s['primary_label'] !== '')<a href="{{ $s['primary_url'] ?: '#' }}" class="btn btn-primary btn-lg">{{ $s['primary_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>@endif
                    @if ($s['secondary_label'] !== '')<a href="{{ $s['secondary_url'] ?: '#' }}" class="btn btn-secondary btn-lg">{{ $s['secondary_label'] }}</a>@endif
                </div>
            @endif
            @if (! empty($s['checklist']))
                <ul class="mt-7 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-600 {{ $centered ? 'justify-center' : '' }}">
                    @foreach ($s['checklist'] as $item)
                        <li class="flex items-center gap-1.5"><i class="ph-fill ph-check-circle text-lg text-tech-blue" aria-hidden="true"></i> {{ $item }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        @if ($visual === 'mockup')
            @include('front.t-one.blocks._mockup')
        @elseif ($visual === 'image')
            <div class="lg:col-span-6">
                <img src="{{ $image }}" alt="{{ $s['title'] }}" class="mx-auto w-full max-w-[34rem] rounded-2xl" @if ($first ?? false) fetchpriority="high" @else loading="lazy" @endif>
            </div>
        @endif
    </div>
</section>
