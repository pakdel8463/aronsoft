@php $img = \App\Support\ProductPageSchema::imageUrl($s['image']); @endphp
<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x grid items-center gap-10 lg:gap-14 {{ $img || ($preview ?? false) ? 'lg:grid-cols-2' : '' }}">
        <div class="reveal {{ $img && $s['image_side'] === 'start' ? 'lg:order-2' : '' }}">
            @if ($s['eyebrow'] !== '')<span class="eyebrow">{{ $s['eyebrow'] }}</span>@endif
            @if ($s['title'] !== '')<h2 id="{{ $anchor }}-title" class="h2-section">{{ $s['title'] }}</h2>@endif
            @if (trim(strip_tags($s['body'])) !== '')<div class="rich mt-4">{!! \App\Forms\Shortcodes::render($s['body']) !!}</div>@endif
            @if (! empty($s['checklist']))
                <ul class="check-list mt-5 space-y-1">
                    @foreach ($s['checklist'] as $item)<li>{{ $item }}</li>@endforeach
                </ul>
            @endif
            @if ($s['button_label'] !== '')
                <a href="{{ $s['button_url'] ?: '#' }}" class="btn btn-primary btn-lg mt-7">{{ $s['button_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
            @endif
        </div>
        @if ($img || ($preview ?? false))
        <div class="reveal {{ $s['image_side'] === 'start' ? 'lg:order-1' : '' }}">
            @if ($img)
                <img src="{{ $img }}" alt="{{ $s['title'] }}" class="w-full rounded-2xl border border-slate-200 object-cover" loading="lazy">
            @else
                <div class="grid min-h-[16rem] place-items-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 text-slate-300" aria-hidden="true"><i class="ph ph-image text-6xl"></i></div>
            @endif
        </div>
        @endif
    </div>
</section>
