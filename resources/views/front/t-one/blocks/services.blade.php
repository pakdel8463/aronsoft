@php $cols = ['two' => 'sm:grid-cols-2', 'three' => 'sm:grid-cols-2 lg:grid-cols-3', 'four' => 'sm:grid-cols-2 lg:grid-cols-4'][$s['columns']] ?? 'sm:grid-cols-2 lg:grid-cols-3'; @endphp
<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        @include('front.t-one.blocks._head')
        <ul class="grid gap-5 {{ $cols }}">
            @foreach ($s['items'] as $item)
                <li class="reveal">
                    <article class="group card {{ $item['url'] !== '' ? 'card-hover' : '' }} relative flex h-full flex-col p-6">
                        @if ($item['icon'] !== '')<span class="icon-tile mb-4"><i class="ph {{ $item['icon'] }}" aria-hidden="true"></i></span>@endif
                        <h3 class="text-lg font-bold text-slate-900">{{ $item['title'] }}</h3>
                        @if ($item['text'] !== '')<p class="mt-2 flex-1 text-sm leading-7 text-slate-600">{{ $item['text'] }}</p>@endif
                        @if ($item['url'] !== '')
                            <a href="{{ $item['url'] }}" class="link-arrow mt-3 after:absolute after:inset-0 after:content-['']">{{ $item['link_label'] ?: 'بیشتر بخوانید' }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                        @endif
                    </article>
                </li>
            @endforeach
        </ul>
    </div>
</section>
