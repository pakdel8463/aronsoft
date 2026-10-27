@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'required' => false,
    'for' => null,
])
@php
    $key = $name ? trim(str_replace(['[', ']'], ['.', ''], $name), '.') : null;
    $message = $key ? $errors->first($key) : null;
@endphp
<div {{ $attributes->class(['min-w-0']) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="a-label">{{ $label }}@if ($required)<span class="ms-1 text-coral-500">*</span>@endif</label>
    @endif
    {{ $slot }}
    @if ($message)
        <p class="mt-1.5 flex items-start gap-1.5 text-xs font-semibold leading-5 text-coral-600"><i class="ph-fill ph-warning-circle mt-0.5"></i>{{ $message }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs leading-5 text-slate-400">{{ $hint }}</p>
    @endif
</div>
