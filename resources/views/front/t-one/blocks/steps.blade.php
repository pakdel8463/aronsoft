@php $n = count($s['items']); @endphp
<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        @include('front.t-one.blocks._head')
        <ol class="grid gap-5 sm:grid-cols-2 {{ $n >= 4 ? 'lg:grid-cols-4' : ($n === 3 ? 'lg:grid-cols-3' : '') }}">
            @foreach ($s['items'] as $i => $item)
                <li class="reveal">
                    <div class="card h-full p-6">
                        <span class="step-num num" aria-hidden="true">{{ \App\Helpers\Fa::toFa((string) ($i + 1)) }}</span>
                        <h3 class="mt-4 text-base font-bold text-slate-900">{{ $item['title'] }}</h3>
                        @if ($item['text'] !== '')<p class="mt-2 text-sm leading-7 text-slate-600">{{ $item['text'] }}</p>@endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
