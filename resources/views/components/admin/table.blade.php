@props([
    'hover' => true,
    'flush' => false,
])
<div {{ $attributes->class(['overflow-x-auto', 'px-3 pb-3 sm:px-4' => ! $flush]) }}>
    <table class="a-table {{ $hover ? 'is-hover' : '' }}">
        @isset($head)
            <thead><tr>{{ $head }}</tr></thead>
        @endisset
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
