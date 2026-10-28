<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        @include('front.t-one.blocks._head')
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($s['items'] as $item)
                @php $avatar = \App\Support\ProductPageSchema::imageUrl($item['avatar']); @endphp
                <figure class="reveal card flex h-full flex-col p-6">
                    <i class="ph-fill ph-quotes text-3xl text-brand-200" aria-hidden="true"></i>
                    <blockquote class="mt-3 flex-1 text-sm leading-8 text-slate-700">{{ $item['text'] }}</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3 border-t border-slate-100 pt-4">
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="" class="h-11 w-11 rounded-full object-cover" loading="lazy">
                        @else
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-50 font-extrabold text-tech-blue" aria-hidden="true">{{ mb_substr($item['name'], 0, 1) }}</span>
                        @endif
                        <span class="min-w-0">
                            <span class="block text-sm font-bold text-slate-900">{{ $item['name'] }}</span>
                            @if ($item['role'] !== '')<span class="block text-xs text-slate-500">{{ $item['role'] }}</span>@endif
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
