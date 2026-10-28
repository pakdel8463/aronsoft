@php
    $slides = array_values(array_filter($s['slides'] ?? [], fn ($x) => ($x['image'] ?? '') !== '' || ($x['title'] ?? '') !== ''));
    $n = count($slides);
    $delay = $s['autoplay'] && $n > 1 ? max(3, (int) $s['interval']) * 1000 : 0;
@endphp
@if ($n)
    @include('front.t-one.blocks._assets')
    <section id="{{ $anchor }}" class="bk-slider bk-h-{{ $s['height'] }} {{ $s['contained'] ? 'is-contained' : '' }}"
             aria-roledescription="carousel" aria-label="{{ $slides[0]['title'] ?: 'اسلایدر' }}" data-carousel data-autoplay="{{ $delay }}">
        <div class="bk-slider-frame">
            <div class="bk-track" data-track>
                @foreach ($slides as $i => $slide)
                    @php
                        $img = \App\Support\ProductPageSchema::imageUrl($slide['image'] ?? '');
                        $url = $slide['button_url'] ?? '';
                        $tag = ($first ?? false) && $i === 0 ? 'h1' : 'h2';
                    @endphp
                    <div class="bk-slide" role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} از {{ $n }}" data-slide>
                        @if ($img)
                            <img src="{{ $img }}" alt="{{ $slide['title'] }}" class="bk-slide-img" decoding="async"
                                 @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                        @else
                            <div class="bk-slide-img bk-slide-fallback bk-fallback-{{ $i % 3 }}" aria-hidden="true"></div>
                        @endif
                        <div class="bk-slide-shade bk-shade-{{ $img ? $s['overlay'] : 'dark' }}" aria-hidden="true"></div>
                        <div class="container-x bk-slide-body bk-align-{{ $s['align'] }}">
                            <div class="bk-slide-content">
                                @if (($slide['eyebrow'] ?? '') !== '')<span class="bk-slide-eyebrow">{{ $slide['eyebrow'] }}</span>@endif
                                @if ($slide['title'] !== '')<{{ $tag }} class="bk-slide-title">{{ $slide['title'] }}</{{ $tag }}>@endif
                                @if (($slide['text'] ?? '') !== '')<p class="bk-slide-text">{!! nl2br(e($slide['text'])) !!}</p>@endif
                                @if (($slide['button_label'] ?? '') !== '')
                                    <a href="{{ $url ?: '#' }}" class="btn btn-primary btn-lg bk-slide-btn" @if ($i > 0) tabindex="-1" @endif data-slide-focusable>{{ $slide['button_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                                @endif
                            </div>
                        </div>
                        @if ($url !== '' && ($slide['button_label'] ?? '') === '')
                            <a href="{{ $url }}" class="bk-slide-link" aria-label="{{ $slide['title'] ?: 'مشاهده' }}" @if ($i > 0) tabindex="-1" @endif data-slide-focusable></a>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($n > 1)
                <button type="button" class="bk-arrow bk-arrow-prev" data-prev aria-label="اسلاید قبلی"><i class="ph ph-caret-right" aria-hidden="true"></i></button>
                <button type="button" class="bk-arrow bk-arrow-next" data-next aria-label="اسلاید بعدی"><i class="ph ph-caret-left" aria-hidden="true"></i></button>
                <div class="bk-dots">
                    @if ($delay)
                        <button type="button" class="bk-pause" data-pause aria-label="توقف پخش خودکار"><i class="ph ph-pause" aria-hidden="true"></i></button>
                    @endif
                    @foreach ($slides as $i => $slide)
                        <button type="button" class="bk-dot" data-dot="{{ $i }}" aria-label="اسلاید {{ $i + 1 }}" @if ($i === 0) aria-current="true" @endif></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@elseif ($preview ?? false)
    <div id="{{ $anchor }}" class="container-x py-6"><p class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">بلوک «اسلایدر»: هنوز اسلایدی با تصویر یا عنوان ندارد.</p></div>
@endif
{{-- آیکن‌های اسکریپت: ph-play ph-pause --}}
