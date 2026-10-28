<section id="{{ $anchor }}" class="section" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-12 text-center sm:px-12 md:py-16">
            <div class="pointer-events-none absolute inset-0 opacity-30" style="background: radial-gradient(60% 80% at 50% 0%, #2f4ff5, transparent 70%)" aria-hidden="true"></div>
            <div class="relative mx-auto max-w-2xl">
                @if ($s['title'] !== '')<h2 id="{{ $anchor }}-title" class="h2-section !text-white">{{ $s['title'] }}</h2>@endif
                @if ($s['text'] !== '')<p class="mt-3 text-base leading-8 text-slate-300">{{ $s['text'] }}</p>@endif
                @if ($s['primary_label'] !== '' || $s['secondary_label'] !== '')
                    <div class="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        @if ($s['primary_label'] !== '')<a href="{{ $s['primary_url'] ?: '#' }}" class="btn btn-lg w-full bg-white text-slate-900 hover:bg-slate-100 sm:w-auto">{{ $s['primary_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>@endif
                        @if ($s['secondary_label'] !== '')<a href="{{ $s['secondary_url'] ?: '#' }}" class="btn btn-lg w-full border border-white/30 text-white hover:bg-white/10 sm:w-auto">{{ $s['secondary_label'] }}</a>@endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
