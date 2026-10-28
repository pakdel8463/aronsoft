<section id="{{ $anchor }}" class="border-y border-slate-200 bg-slate-50" aria-label="{{ $s['title'] ?: 'تکنولوژی‌ها' }}">
    <div class="container-x py-7">
        @if ($s['title'] !== '')<p class="mb-4 text-center text-xs font-bold text-slate-500">{{ $s['title'] }}</p>@endif
        <ul class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3">
            @foreach ($s['items'] as $item)
                <li class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-bold text-slate-700" dir="auto">
                    @if ($item['icon'] !== '')<i class="ph {{ $item['icon'] }} text-lg text-tech-blue" aria-hidden="true"></i>@endif
                    {{ $item['label'] }}
                </li>
            @endforeach
        </ul>
    </div>
</section>
