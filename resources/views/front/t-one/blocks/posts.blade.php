@php
    $limit = ['three' => 3, 'six' => 6][$s['count']] ?? 3;
    $posts = \App\Models\Post::published()->with('categories')
        ->orderByRaw('COALESCE(published_at, created_at) DESC')->orderByDesc('id')->take($limit)->get();
@endphp
@if ($posts->isNotEmpty())
    <section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
        <div class="container-x">
            <div class="mb-8 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    @if ($s['eyebrow'] !== '')<span class="eyebrow">{{ $s['eyebrow'] }}</span>@endif
                    @if ($s['title'] !== '')<h2 id="{{ $anchor }}-title" class="h2-section">{{ $s['title'] }}</h2>@endif
                    @if ($s['description'] !== '')<p class="mt-2 text-slate-600">{{ $s['description'] }}</p>@endif
                </div>
                @if ($s['link_label'] !== '')<a href="{{ route('blog.index') }}" class="link-arrow">{{ $s['link_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>@endif
            </div>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <div class="reveal">@include('front.t-one.partials.post-card', ['post' => $post])</div>
                @endforeach
            </div>
        </div>
    </section>
@elseif ($preview ?? false)
    <div id="{{ $anchor }}" class="container-x py-6"><p class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">بلوک «آخرین مقالات»: هنوز مقاله‌ی منتشرشده‌ای نیست؛ این بلوک برای بازدیدکنندگان نمایش داده نمی‌شود.</p></div>
@endif
