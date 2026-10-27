@props([
    'name',
    'title' => null,
    'maxWidth' => 'lg',
    'show' => false,
])
@php
    $w = ['sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg', 'xl' => 'sm:max-w-xl', '2xl' => 'sm:max-w-2xl'][$maxWidth] ?? 'sm:max-w-lg';
@endphp
{{-- Vanilla JS. Open: <button data-modal-open="name">; close: [data-modal-close] inside; JS: adminModal.open('name') / .close('name'). --}}
<div data-modal="{{ $name }}" class="fixed inset-0 z-[90] items-end justify-center sm:items-center sm:p-6 {{ $show ? 'flex' : 'hidden' }}" role="dialog" aria-modal="true" @if ($title) aria-label="{{ $title }}" @endif>
    <div data-modal-close class="absolute inset-0 bg-rail/60 backdrop-blur-[2px]"></div>
    <div data-modal-panel class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-[1.75rem] bg-white shadow-adm-pop sm:rounded-[var(--radius-card)] {{ $w }}">
        @if ($title)
            <header class="flex items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
                <h3 class="text-base font-extrabold text-ink">{{ $title }}</h3>
                <button type="button" data-modal-close class="grid size-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="بستن"><i class="ph ph-x text-lg"></i></button>
            </header>
        @endif
        <div class="min-h-0 flex-1 overflow-y-auto p-5 sm:p-6">{{ $slot }}</div>
        @isset($footer)
            <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-line bg-slate-50/70 px-5 py-3.5 sm:px-6">{{ $footer }}</footer>
        @endisset
    </div>
</div>
