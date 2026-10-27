@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconOnly' => false,
    'href' => null,
    'type' => 'button',
    'loading' => false,
])
@php
    $classes = 'a-btn a-btn-' . $variant . match ($size) { 'sm' => ' a-btn-sm', 'lg' => ' a-btn-lg', default => '' } . ($iconOnly ? ' a-btn-icon' : '');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<i class="ph ph-{{ $icon }} text-[1.15em]"></i>@endif{{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }} @if ($loading) data-loading-on-submit @endif>
        @if ($icon)<i class="ph ph-{{ $icon }} text-[1.15em]"></i>@endif{{ $slot }}
    </button>
@endif
