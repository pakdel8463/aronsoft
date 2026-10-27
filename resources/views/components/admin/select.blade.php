@props([
    'name',
    'options' => [],
    'selected' => null,
    'label' => null,
    'hint' => null,
    'required' => false,
    'id' => null,
    'placeholder' => null,
])
@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $id ?: 'f_' . str_replace(['.', '[', ']'], '_', $name);
    $hasError = $errors->has($key);
    $current = array_map('strval', (array) old($key, $selected));
    $wrap = $label || $hint;
@endphp
@if ($wrap)<x-admin.field :label="$label" :name="$name" :hint="$hint" :required="$required" :for="$id">@endif
    <select name="{{ $name }}" id="{{ $id }}" @if ($required) required @endif
            {{ $attributes->class(['a-select', 'has-error' => $hasError]) }}>
        @if (! is_null($placeholder))<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected(in_array((string) $optValue, $current, true))>{{ $optLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>
    @if (! $wrap && $hasError)<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $errors->first($key) }}</p>@endif
@if ($wrap)</x-admin.field>@endif
