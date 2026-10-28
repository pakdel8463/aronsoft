@php
    $cols = ['two' => 'sm:grid-cols-2', 'three' => 'sm:grid-cols-2 lg:grid-cols-3', 'four' => 'grid-cols-2 lg:grid-cols-4'][$s['columns']] ?? 'sm:grid-cols-2 lg:grid-cols-3';
    $items = collect($s['items'])->map(fn ($i) => $i + ['src' => \App\Support\ProductPageSchema::imageUrl($i['image'])])->filter(fn ($i) => $i['src'])->values();
@endphp
@if ($items->isNotEmpty() || ($preview ?? false))
    <section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
        <div class="container-x">
            @include('front.t-one.blocks._head')
            @if ($items->isEmpty())
                <p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">هنوز تصویری به گالری اضافه نشده است.</p>
            @else
                <div class="grid gap-4 {{ $cols }}">
                    @foreach ($items as $item)
                        <figure class="reveal overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <a href="{{ $item['src'] }}" target="_blank" rel="noopener" class="block"><img src="{{ $item['src'] }}" alt="{{ $item['caption'] }}" class="aspect-media w-full object-cover transition duration-300 hover:scale-[1.03]" loading="lazy"></a>
                            @if ($item['caption'] !== '')<figcaption class="px-4 py-3 text-sm text-slate-600">{{ $item['caption'] }}</figcaption>@endif
                        </figure>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
