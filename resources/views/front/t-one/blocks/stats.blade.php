@php $dark = $s['style'] === 'dark'; $n = max(1, count($s['items'])); @endphp
<section id="{{ $anchor }}" class="section {{ $dark ? 'bg-slate-900' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x">
        @if ($s['eyebrow'] !== '' || $s['title'] !== '')
            <div class="section-head reveal">
                @if ($s['eyebrow'] !== '')<span class="eyebrow">{{ $s['eyebrow'] }}</span>@endif
                @if ($s['title'] !== '')<h2 id="{{ $anchor }}-title" class="h2-section {{ $dark ? '!text-white' : '' }}">{{ $s['title'] }}</h2>@endif
            </div>
        @endif
        <dl class="grid grid-cols-2 gap-4 {{ $n >= 4 ? 'lg:grid-cols-4' : ($n === 3 ? 'lg:grid-cols-3' : '') }}">
            @foreach ($s['items'] as $item)
                <div class="reveal rounded-2xl p-6 text-center {{ $dark ? 'border border-white/10 bg-white/5' : 'card' }}">
                    @if ($item['icon'] !== '')<i class="ph {{ $item['icon'] }} mb-2 block text-3xl {{ $dark ? 'text-blue-300' : 'text-tech-blue' }}" aria-hidden="true"></i>@endif
                    <dd class="num text-3xl font-extrabold {{ $dark ? 'text-white' : 'text-slate-900' }}" dir="auto">{{ $item['value'] }}</dd>
                    <dt class="mt-1 text-sm {{ $dark ? 'text-slate-300' : 'text-slate-600' }}">{{ $item['label'] }}</dt>
                </div>
            @endforeach
        </dl>
    </div>
</section>
