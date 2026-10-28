@php
    $limit = max(3, min(16, (int) $s['count']));
    $cards = collect();
    $allUrl = null;
    if ($s['source'] === 'posts') {
        $cards = \App\Models\Post::published()->with('categories')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')->orderByDesc('id')->take($limit)->get();
        $allUrl = route('blog.index');
    } elseif ($s['source'] === 'portfolio') {
        $cards = \App\Models\Portfolio::where('status', 'published')->orderByDesc('created_at')->take($limit)->get();
        $allUrl = route('portfolio.index');
    } else {
        $cards = collect($s['items'] ?? [])->filter(fn ($c) => ($c['title'] ?? '') !== '')->values();
        $allUrl = $s['link_url'] ?: null;
    }
    $n = $cards->count();
@endphp
@if ($n)
    @include('front.t-one.blocks._assets')
    <section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
        <div class="container-x" data-carousel data-autoplay="{{ $s['autoplay'] ? 5000 : 0 }}" data-cards aria-roledescription="carousel" aria-label="{{ $s['title'] ?: 'کارت‌ها' }}">
            <div class="bk-cards-head">
                <div>
                    @if ($s['eyebrow'] !== '')<span class="eyebrow">{{ $s['eyebrow'] }}</span>@endif
                    @if ($s['title'] !== '')<h2 id="{{ $anchor }}-title" class="h2-section">{{ $s['title'] }}</h2>@endif
                    @if ($s['description'] !== '')<p class="mt-2 text-slate-600">{{ $s['description'] }}</p>@endif
                </div>
                <div class="bk-cards-nav">
                    @if ($s['link_label'] !== '' && $allUrl)<a href="{{ $allUrl }}" class="link-arrow">{{ $s['link_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>@endif
                    @if ($n > 1)
                        <button type="button" class="bk-nav-btn" data-prev aria-label="قبلی"><i class="ph ph-caret-right" aria-hidden="true"></i></button>
                        <button type="button" class="bk-nav-btn" data-next aria-label="بعدی"><i class="ph ph-caret-left" aria-hidden="true"></i></button>
                    @endif
                </div>
            </div>
            <div class="bk-track bk-cards bk-per-{{ $s['per_view'] }}" data-track>
                @foreach ($cards as $i => $card)
                    <div class="bk-card-slide" role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} از {{ $n }}" data-slide>
                        @if ($s['source'] === 'posts')
                            @include('front.t-one.partials.post-card', ['post' => $card])
                        @elseif ($s['source'] === 'portfolio')
                            @include('front.t-one.partials.portfolio-card', ['p' => $card])
                        @else
                            @php $img = \App\Support\ProductPageSchema::imageUrl($card['image'] ?? ''); @endphp
                            <article class="group card card-hover relative flex h-full flex-col overflow-hidden">
                                <div class="relative aspect-[16/9] overflow-hidden border-b border-slate-100 bg-slate-100">
                                    @if ($img)
                                        <img src="{{ $img }}" alt="{{ $card['title'] }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-4xl text-slate-300" aria-hidden="true"><i class="ph ph-newspaper"></i></div>
                                    @endif
                                </div>
                                <div class="flex flex-1 flex-col p-5">
                                    @if (($card['badge'] ?? '') !== '' || ($card['date'] ?? '') !== '')
                                        <div class="mb-2 flex items-center gap-2 text-xs">
                                            @if (($card['badge'] ?? '') !== '')<span class="chip chip-blue">{{ $card['badge'] }}</span>@endif
                                            @if (($card['date'] ?? '') !== '')<span class="text-slate-500">{{ $card['date'] }}</span>@endif
                                        </div>
                                    @endif
                                    <h3 class="line-clamp-2 text-[1.0625rem] font-bold leading-8 text-slate-900" dir="auto">
                                        @if (($card['url'] ?? '') !== '')
                                            <a href="{{ $card['url'] }}" class="after:absolute after:inset-0 after:content-['']">{{ $card['title'] }}</a>
                                        @else
                                            {{ $card['title'] }}
                                        @endif
                                    </h3>
                                    @if (($card['text'] ?? '') !== '')<p class="mt-1.5 line-clamp-3 text-sm leading-7 text-slate-600">{{ $card['text'] }}</p>@endif
                                    @if (($card['url'] ?? '') !== '')<span class="link-arrow mt-auto pt-3" aria-hidden="true">ادامه <i class="ph ph-arrow-left"></i></span>@endif
                                </div>
                            </article>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@elseif ($preview ?? false)
    <div id="{{ $anchor }}" class="container-x py-6"><p class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">بلوک «اسلایدر کارت»: کارتی برای نمایش نیست ({{ ['posts' => 'هنوز مقاله‌ی منتشرشده‌ای نیست', 'portfolio' => 'هنوز نمونه‌کار منتشرشده‌ای نیست', 'manual' => 'کارت دستی با عنوان اضافه کنید'][$s['source']] ?? '' }}).</p></div>
@endif
