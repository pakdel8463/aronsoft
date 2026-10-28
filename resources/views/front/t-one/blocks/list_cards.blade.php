<section id="{{ $anchor }}" class="section {{ $s['alt'] ? 'section-alt' : '' }}" @if ($s['title'] !== '') aria-labelledby="{{ $anchor }}-title" @endif>
    <div class="container-x grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
        <div class="reveal">
            @if ($s['eyebrow'] !== '')<span class="eyebrow">{{ $s['eyebrow'] }}</span>@endif
            @if ($s['title'] !== '')<h2 id="{{ $anchor }}-title" class="h2-section">{{ $s['title'] }}</h2>@endif
            @if ($s['description'] !== '')<p class="lead mt-4">{{ $s['description'] }}</p>@endif
            @if (! empty($s['checklist']))
                <ul class="check-list mt-5 space-y-1">
                    @foreach ($s['checklist'] as $item)<li>{{ $item }}</li>@endforeach
                </ul>
            @endif
            @if ($s['button_label'] !== '')
                <a href="{{ $s['button_url'] ?: '#' }}" class="btn btn-dark btn-lg mt-7">{{ $s['button_label'] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
            @endif
        </div>
        @if (! empty($s['items']))
            <ul class="grid gap-3.5">
                @foreach ($s['items'] as $item)
                    <li class="reveal">
                        <div class="card flex items-center gap-4 p-4 sm:p-5">
                            @if ($item['icon'] !== '')<span class="icon-tile"><i class="ph {{ $item['icon'] }}" aria-hidden="true"></i></span>@endif
                            <div class="min-w-0 flex-1">
                                <h3 class="text-[0.9375rem] font-bold text-slate-900">{{ $item['title'] }}</h3>
                                @if ($item['meta'] !== '')<p class="mt-0.5 text-sm text-slate-600">{{ $item['meta'] }}</p>@endif
                            </div>
                            @if ($item['chip'] !== '')<span class="chip chip-blue shrink-0">{{ $item['chip'] }}</span>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
