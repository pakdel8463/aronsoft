@props([
    'href' => null,
    'icon' => null,
    'danger' => false,
    'type' => 'button',
])
@php
    $cls = 'flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-start text-sm font-semibold transition ' . ($danger ? 'text-coral-600 hover:bg-coral-50' : 'text-slate-700 hover:bg-aron-50 hover:text-aron-700');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($cls) }}>@if ($icon)<i class="ph ph-{{ $icon }} text-lg"></i>@endif{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($cls) }}>@if ($icon)<i class="ph ph-{{ $icon }} text-lg"></i>@endif{{ $slot }}</button>
@endif
