@props([
    'label',
    'value',
    'icon' => 'chart-bar',
    'color' => 'brand',
    'hint' => null,
    'delta' => null,
    'deltaType' => 'up',
    'href' => null,
])
@php
    $tint = [
        'brand' => 'bg-aron-50 text-aron-600',
        'mint' => 'bg-mint-50 text-mint-600',
        'coral' => 'bg-coral-50 text-coral-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'violet' => 'bg-violet-50 text-violet-600',
    ][$color] ?? 'bg-aron-50 text-aron-600';
    $deltaCls = ['up' => 'bg-mint-50 text-mint-700', 'down' => 'bg-coral-50 text-coral-700', 'neutral' => 'bg-slate-100 text-slate-600'][$deltaType] ?? 'bg-slate-100 text-slate-600';
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class(['a-card group relative block p-5 transition', 'hover:-translate-y-0.5 hover:shadow-adm-lift' => $href]) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="grid size-12 place-items-center rounded-2xl text-2xl {{ $tint }}"><i class="ph-fill ph-{{ $icon }}"></i></span>
        @if ($delta)<span class="rounded-full px-2 py-1 text-[11px] font-extrabold leading-none {{ $deltaCls }}">{{ $delta }}</span>@endif
    </div>
    <div class="mt-4 text-3xl font-black leading-none tracking-tight text-ink">{{ $value }}</div>
    <div class="mt-2 text-sm font-semibold text-slate-500">{{ $label }}</div>
    @if ($hint)<div class="mt-1 text-xs text-slate-400">{{ $hint }}</div>@endif
</{{ $tag }}>
