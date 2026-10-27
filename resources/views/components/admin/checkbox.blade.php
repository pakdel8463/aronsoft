@props([
    'name',
    'label' => null,
    'hint' => null,
    'value' => '1',
    'checked' => false,
    'switch' => false,
    'unchecked' => null,
    'id' => null,
    'type' => 'checkbox',
])
@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $id ?: 'f_' . str_replace(['.', '[', ']'], '_', $name) . ($type === 'radio' || str_ends_with($name, '[]') ? '_' . \Illuminate\Support\Str::slug((string) $value, '_') : '');
    $isChecked = session()->hasOldInput()
        ? in_array((string) $value, array_map('strval', (array) old($key, [])), true)
        : (bool) $checked;
@endphp
<label for="{{ $id }}" class="flex cursor-pointer items-start gap-3">
    @if (! is_null($unchecked))<input type="hidden" name="{{ $name }}" value="{{ $unchecked }}">@endif
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($isChecked)
           {{ $attributes->class([$switch ? 'a-switch' : 'a-check', $switch ? 'mt-0.5' : 'mt-px']) }}>
    @if ($label || $hint)
        <span class="min-w-0">
            @if ($label)<span class="block text-sm font-bold leading-6 text-slate-700">{{ $label }}</span>@endif
            @if ($hint)<span class="block text-xs leading-5 text-slate-400">{{ $hint }}</span>@endif
            @if ($errors->has($key))<span class="block text-xs font-semibold text-coral-600">{{ $errors->first($key) }}</span>@endif
        </span>
    @endif
</label>
