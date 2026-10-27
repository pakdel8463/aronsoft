@props([
    'color' => 'slate',
    'dot' => false,
    'icon' => null,
])
@php
    $map = [
        'brand' => ['bg-aron-50 text-aron-700', 'bg-aron-500'],
        'mint' => ['bg-mint-50 text-mint-700', 'bg-mint-500'],
        'coral' => ['bg-coral-50 text-coral-700', 'bg-coral-500'],
        'amber' => ['bg-amber-50 text-amber-700', 'bg-amber-500'],
        'sky' => ['bg-sky-50 text-sky-700', 'bg-sky-500'],
        'violet' => ['bg-violet-50 text-violet-700', 'bg-violet-500'],
        'slate' => ['bg-slate-100 text-slate-600', 'bg-slate-400'],
    ];
    [$cls, $dotCls] = $map[$color] ?? $map['slate'];
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold leading-none', $cls]) }}>
    @if ($dot)<span class="size-1.5 rounded-full {{ $dotCls }}"></span>@endif
    @if ($icon)<i class="ph ph-{{ $icon }}"></i>@endif
    {{ $slot }}
</span>
