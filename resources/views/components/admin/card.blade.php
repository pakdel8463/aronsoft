@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'padding' => true,
])
<section {{ $attributes->class(['a-card overflow-hidden']) }}>
    @if ($title || isset($actions))
        <header class="flex flex-wrap items-center justify-between gap-3 px-5 pt-5 sm:px-6 sm:pt-6 {{ $padding ? '' : 'pb-4' }}">
            <div class="flex min-w-0 items-center gap-3">
                @if ($icon)
                    <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-aron-50 text-xl text-aron-600"><i class="ph ph-{{ $icon }}"></i></span>
                @endif
                <div class="min-w-0">
                    @if ($title)<h2 class="truncate text-base font-extrabold text-ink">{{ $title }}</h2>@endif
                    @if ($subtitle)<p class="mt-0.5 text-xs leading-5 text-slate-500">{{ $subtitle }}</p>@endif
                </div>
            </div>
            @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div @class(['p-5 sm:p-6' => $padding && ! ($title || isset($actions)), 'px-5 pb-5 pt-4 sm:px-6 sm:pb-6' => $padding && ($title || isset($actions))])>
        {{ $slot }}
    </div>
    @isset($footer)
        <footer class="border-t border-line bg-slate-50/60 px-5 py-3.5 sm:px-6">{{ $footer }}</footer>
    @endisset
</section>
