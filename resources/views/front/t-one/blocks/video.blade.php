@php $embed = $s['url'] !== '' ? \App\Support\HtmlSanitizer::embedUrl($s['url']) : null; @endphp
@if ($embed || ($preview ?? false))
    <section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
        <div class="container-x">
            @include('front.t-one.blocks._head')
            <div class="reveal mx-auto max-w-4xl">
                @if ($embed)
                    <div class="aspect-wide overflow-hidden rounded-2xl border border-slate-200 bg-slate-900">
                        <iframe src="{{ $embed }}" title="{{ $s['title'] ?: 'ویدئو' }}" class="h-full w-full" loading="lazy" allowfullscreen allow="autoplay; fullscreen; picture-in-picture"></iframe>
                    </div>
                @else
                    <p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">لینک ویدئو وارد نشده یا از آپارات/یوتیوب نیست؛ این بلوک برای بازدیدکنندگان نمایش داده نمی‌شود.</p>
                @endif
            </div>
        </div>
    </section>
@endif
