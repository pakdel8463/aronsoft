@php $items = array_values(array_filter($s['items'] ?? [], fn ($t) => ($t['title'] ?? '') !== '')); @endphp
@if (count($items))
    @include('front.t-one.blocks._assets')
    <section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
        <div class="container-x">
            @include('front.t-one.blocks._head')
            <ol class="bk-timeline">
                @foreach ($items as $t)
                    <li class="bk-tl-item reveal">
                        <span class="bk-tl-dot" aria-hidden="true"><i class="ph {{ ($t['icon'] ?? '') ?: 'ph-circle' }}"></i></span>
                        <div class="bk-tl-card">
                            @if (($t['date'] ?? '') !== '')<span class="bk-tl-date">{{ $t['date'] }}</span>@endif
                            <h3 class="bk-tl-title">{{ $t['title'] }}</h3>
                            @if (($t['text'] ?? '') !== '')<p class="bk-tl-text">{!! nl2br(e($t['text'])) !!}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endif
