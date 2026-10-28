@php
    $limit = ['three' => 3, 'six' => 6, 'nine' => 9][$s['count']] ?? 6;
    $projects = \App\Models\Portfolio::where('status', 'published')->orderByDesc('created_at')->take($limit)->get();
@endphp
<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        <div class="mb-8 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-end">
            <div>
                @if ($s['eyebrow'] !== '')<span class="eyebrow">{{ $s['eyebrow'] }}</span>@endif
                @if ($s['title'] !== '')<h2 id="{{ $anchor }}-title" class="h2-section">{{ $s['title'] }}</h2>@endif
                @if ($s['description'] !== '')<p class="mt-2 text-slate-600">{{ $s['description'] }}</p>@endif
            </div>
            @if ($s['link_label'] !== '')<a href="{{ route('portfolio.index') }}" class="link-arrow">{{ $s['link_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>@endif
        </div>
        @if ($projects->isEmpty())
            <div class="card p-10 text-center text-slate-600"><i class="ph ph-folder-open mb-3 block text-4xl text-slate-300" aria-hidden="true"></i>به‌زودی نمونه‌کارها در این بخش نمایش داده می‌شود.</div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <div class="reveal">@include('front.t-one.partials.portfolio-card', ['p' => $project])</div>
                @endforeach
            </div>
        @endif
    </div>
</section>
