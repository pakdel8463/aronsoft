@props(['status'])
@if ($status)
    <div {{ $attributes->merge(['class' => 'form-msg is-ok']) }} role="status">{{ $status }}</div>
@endif
