@props([
    'icon' => 'tray',
    'title' => 'موردی پیدا نشد',
    'description' => null,
])
<div {{ $attributes->class(['flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <span class="mb-4 grid size-16 place-items-center rounded-3xl bg-aron-50 text-3xl text-aron-500"><i class="ph ph-{{ $icon }}"></i></span>
    <h3 class="text-base font-extrabold text-ink">{{ $title }}</h3>
    @if ($description)<p class="mt-1.5 max-w-sm text-sm leading-6 text-slate-500">{{ $description }}</p>@endif
    @if (trim((string) $slot) !== '')<div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $slot }}</div>@endif
</div>
