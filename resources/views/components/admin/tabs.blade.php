@props([
    'tabs',
    'default' => null,
])
@php
    $keys = array_keys($tabs);
    $default = $default ?? ($keys[0] ?? '');
    $uid = 'tabs_' . \Illuminate\Support\Str::random(6);
@endphp
{{-- Vanilla JS (data-tabs). The inline style prevents a flash of all panels before JS runs. --}}
<style>[data-tabs="{{ $uid }}"]:not([data-ready]) [data-tab-panel]:not([data-tab-panel="{{ $default }}"]){display:none}</style>
<div data-tabs="{{ $uid }}" data-default="{{ $default }}" {{ $attributes }}>
    <div class="no-scrollbar mb-5 flex gap-1 overflow-x-auto rounded-2xl bg-white p-1.5 shadow-adm" role="tablist">
        @foreach ($tabs as $key => $item)
            @php($tabLabel = is_array($item) ? $item['label'] : $item)
            @php($tabIcon = is_array($item) ? ($item['icon'] ?? null) : null)
            <button type="button" role="tab" data-tab="{{ $key }}" aria-selected="{{ $key === $default ? 'true' : 'false' }}"
                    class="inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $key === $default ? 'bg-aron-600 text-white shadow-adm-brand' : 'text-slate-500 hover:bg-aron-50 hover:text-aron-700' }}">
                @if ($tabIcon)<i class="ph ph-{{ $tabIcon }} text-base"></i>@endif{{ $tabLabel }}
            </button>
        @endforeach
    </div>
    {{ $slot }}
</div>
