@props([
    'name',
    'value' => null,
    'label' => null,
    'hint' => null,
    'required' => false,
    'id' => null,
    'rows' => 4,
])
@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $id ?: 'f_' . str_replace(['.', '[', ']'], '_', $name);
    $hasError = $errors->has($key);
    $wrap = $label || $hint;
@endphp
@if ($wrap)<x-admin.field :label="$label" :name="$name" :hint="$hint" :required="$required" :for="$id">@endif
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" @if ($required) required @endif
              {{ $attributes->class(['a-textarea', 'has-error' => $hasError]) }}>{{ old($key, $value) }}</textarea>
    @if (! $wrap && $hasError)<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $errors->first($key) }}</p>@endif
@if ($wrap)</x-admin.field>@endif
