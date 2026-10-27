@props([
    'name',
    'values' => [],
    'suggestions' => [],
    'label' => null,
    'hint' => null,
    'placeholder' => 'تایپ کنید و Enter بزنید',
])
{{-- ورودی چیپ (تگ/تکنولوژی). Enter، ویرگول یا کاما = افزودن. Backspace روی ورودی خالی = حذف آخری. --}}
@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $current = old($key, $values);
    $current = collect(is_array($current) ? $current : [])->map(fn ($v) => trim((string) $v))->filter()->unique()->values()->all();
    $uid = 'ci_' . \Illuminate\Support\Str::random(6);
@endphp
<x-admin.field :label="$label" :name="$name" :hint="$hint" :for="$uid">
    <div data-chip-input data-name="{{ $name }}[]"
         class="flex min-h-[2.75rem] flex-wrap items-center gap-1.5 rounded-xl border border-line bg-white px-2.5 py-2 focus-within:border-aron-500 focus-within:ring-4 focus-within:ring-aron-100">
        @foreach ($current as $c)
            <span data-chip class="inline-flex items-center gap-1 rounded-lg bg-aron-50 px-2.5 py-1 text-xs font-bold text-aron-700">
                <span>{{ $c }}</span>
                <button type="button" data-chip-remove class="grid size-4 place-items-center rounded text-aron-400 hover:bg-aron-100 hover:text-aron-700" aria-label="حذف"><i class="ph ph-x text-[10px]"></i></button>
                <input type="hidden" name="{{ $name }}[]" value="{{ $c }}">
            </span>
        @endforeach
        <input type="text" id="{{ $uid }}" data-chip-text list="{{ $uid }}_list" autocomplete="off" placeholder="{{ $placeholder }}"
               class="min-w-[8rem] flex-1 border-0 bg-transparent p-0.5 text-sm outline-none focus:ring-0">
        <datalist id="{{ $uid }}_list">@foreach ($suggestions as $s)<option value="{{ $s }}">@endforeach</datalist>
    </div>
</x-admin.field>
@once
<script>
(function () {
    function init(box) {
        if (box.dataset.ready) return; box.dataset.ready = 1;
        const input = box.querySelector('[data-chip-text]');
        const has = v => [...box.querySelectorAll('[data-chip] input')].some(i => i.value === v);
        function add(v) {
            v = v.replace(/[,،]/g, ' ').trim();
            if (!v || has(v)) return;
            const chip = document.createElement('span');
            chip.setAttribute('data-chip', '');
            chip.className = 'inline-flex items-center gap-1 rounded-lg bg-aron-50 px-2.5 py-1 text-xs font-bold text-aron-700';
            const t = document.createElement('span'); t.textContent = v;
            const b = document.createElement('button'); b.type = 'button'; b.setAttribute('data-chip-remove', ''); b.setAttribute('aria-label', 'حذف');
            b.className = 'grid size-4 place-items-center rounded text-aron-400 hover:bg-aron-100 hover:text-aron-700'; b.innerHTML = '<i class="ph ph-x text-[10px]"></i>';
            const h = document.createElement('input'); h.type = 'hidden'; h.name = box.dataset.name; h.value = v;
            chip.append(t, b, h); box.insertBefore(chip, input);
        }
        const flush = () => { input.value.split(/[,،]/).forEach(add); input.value = ''; };
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ',' || e.key === '،') { e.preventDefault(); flush(); }
            else if (e.key === 'Backspace' && !input.value) { box.querySelector('[data-chip]:last-of-type')?.remove(); }
        });
        input.addEventListener('blur', flush);
        input.addEventListener('input', e => { if (e.inputType === 'insertReplacementText') flush(); });
        box.addEventListener('click', e => {
            const rm = e.target.closest('[data-chip-remove]');
            if (rm) rm.closest('[data-chip]').remove(); else input.focus();
        });
    }
    const run = () => document.querySelectorAll('[data-chip-input]').forEach(init);
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', run) : run();
})();
</script>
@endonce
