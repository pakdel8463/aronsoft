@props([
    'name',
    'type' => 'text',
    'value' => null,
    'label' => null,
    'hint' => null,
    'required' => false,
    'id' => null,
    'icon' => null,
    'ltr' => null,
])
@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $id ?: 'f_' . str_replace(['.', '[', ']'], '_', $name);
    $hasError = $errors->has($key);
    $skipOld = in_array($type, ['password', 'file']);
    $val = $skipOld ? null : old($key, $value);
    $forceLtr = $ltr ?? in_array($type, ['email', 'url', 'tel', 'number', 'password']);
    $wrap = $label || $hint;
@endphp
@if ($wrap)<x-admin.field :label="$label" :name="$name" :hint="$hint" :required="$required" :for="$id">@endif
    <div class="relative">
        @if ($icon)<i class="ph ph-{{ $icon }} pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>@endif
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" @if (! is_null($val) && ! $skipOld) value="{{ $val }}" @endif
               @if ($required) required @endif @if ($forceLtr) dir="ltr" @endif
               {{ $attributes->class(['a-input', 'has-error' => $hasError, 'ps-10' => $icon]) }}>
    </div>
    @if (! $wrap && $hasError)<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $errors->first($key) }}</p>@endif
@if ($wrap)</x-admin.field>@endif
