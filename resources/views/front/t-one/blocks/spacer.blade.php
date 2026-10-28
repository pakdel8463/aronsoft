@php $h = ['sm' => 'h-6', 'md' => 'h-12', 'lg' => 'h-24'][$s['size']] ?? 'h-12'; @endphp
<div id="{{ $anchor }}" class="container-x flex items-center {{ $h }}" aria-hidden="true">
    @if ($s['divider'])<hr class="w-full border-slate-200">@endif
</div>
