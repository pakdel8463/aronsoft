@props([
    'align' => 'end',
    'width' => 'w-56',
])
{{-- <x-admin.dropdown><x-slot:trigger><x-admin.button variant="secondary" icon="dots-three">…</x-admin.button></x-slot:trigger> <x-admin.dropdown-item …/> </x-admin.dropdown>  (vanilla JS: data-dropdown) --}}
<div data-dropdown {{ $attributes->class(['relative inline-block']) }}>
    <div data-dropdown-trigger aria-expanded="false">{{ $trigger }}</div>
    <div data-dropdown-panel class="absolute top-full z-50 mt-2 hidden {{ $align === 'start' ? 'start-0' : 'end-0' }} {{ $width }} max-w-[88vw] rounded-2xl border border-line bg-white p-1.5 shadow-adm-pop">
        {{ $slot }}
    </div>
</div>
