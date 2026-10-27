@props([
    'name' => 'gallery',
    'items' => [],
    'uploadUrl',
    'extra' => [],
    'label' => null,
    'hint' => null,
    'max' => 20,
])
{{-- گالری تصاویر: آپلود چندتایی، جابه‌جایی با کشیدن (یا دکمه‌های ‹ ›)، حذف. Vanilla JS. --}}
@php
    $uid = 'gf_' . \Illuminate\Support\Str::random(6);
    $old = old($name);
    if (is_array($old)) {
        $byValue = collect($items)->keyBy(fn ($i) => (string) $i['value']);
        $items = collect($old)->map(fn ($v) => $byValue[(string) $v] ?? (is_numeric($v) ? null : ['value' => $v, 'url' => preg_match('#^(https?:)?//#', $v) ? $v : asset(ltrim($v, '/'))]))->filter()->values()->all();
    }
@endphp
<x-admin.field :label="$label" :name="$name" :hint="$hint" :for="$uid">
    <div data-gallery data-name="{{ $name }}[]" data-upload="{{ $uploadUrl }}" data-extra='@json($extra)' data-max="{{ $max }}">
        <input type="hidden" name="{{ $name }}_submitted" value="1">
        <div data-g-grid class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($items as $it)
                <div data-g-tile draggable="true" class="group relative overflow-hidden rounded-2xl border border-line bg-slate-50 shadow-adm">
                    <img src="{{ $it['url'] }}" alt="" class="aspect-[4/3] w-full object-cover" draggable="false">
                    <input type="hidden" name="{{ $name }}[]" value="{{ $it['value'] }}">
                    <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-gradient-to-t from-black/60 to-transparent p-1.5">
                        <span class="flex gap-1">
                            <button type="button" data-g-prev class="grid size-7 place-items-center rounded-lg bg-white/90 text-slate-700 hover:bg-white" aria-label="جابه‌جایی به عقب"><i class="ph ph-caret-right"></i></button>
                            <button type="button" data-g-next class="grid size-7 place-items-center rounded-lg bg-white/90 text-slate-700 hover:bg-white" aria-label="جابه‌جایی به جلو"><i class="ph ph-caret-left"></i></button>
                        </span>
                        <button type="button" data-g-remove class="grid size-7 place-items-center rounded-lg bg-coral-500 text-white hover:bg-coral-600" aria-label="حذف"><i class="ph ph-trash"></i></button>
                    </div>
                </div>
            @endforeach
            <label for="{{ $uid }}" data-g-drop class="flex aspect-[4/3] cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 p-2 text-center text-xs font-semibold text-slate-500 transition hover:border-aron-400 hover:bg-aron-50/50">
                <i class="ph ph-images text-2xl text-aron-500" data-g-icon></i>
                <span data-g-text>افزودن تصویر<br><span class="font-normal text-slate-400">بکشید و رها کنید</span></span>
            </label>
        </div>
        <input type="file" id="{{ $uid }}" accept="image/jpeg,image/png,image/webp" multiple class="sr-only" data-g-file>
    </div>
</x-admin.field>
@once
<script>
(function () {
    function init(box) {
        if (box.dataset.ready) return; box.dataset.ready = 1;
        const grid = box.querySelector('[data-g-grid]'), drop = box.querySelector('[data-g-drop]'),
              file = box.querySelector('[data-g-file]'), icon = box.querySelector('[data-g-icon]'), txt = box.querySelector('[data-g-text]');
        const extra = JSON.parse(box.dataset.extra || '{}'), max = +box.dataset.max || 20;
        const tiles = () => [...grid.querySelectorAll('[data-g-tile]')];
        const idleHtml = txt.innerHTML;
        function tile(f) {
            const d = document.createElement('div');
            d.setAttribute('data-g-tile', ''); d.draggable = true;
            d.className = 'group relative overflow-hidden rounded-2xl border border-line bg-slate-50 shadow-adm';
            d.innerHTML = '<img alt="" class="aspect-[4/3] w-full object-cover" draggable="false"><input type="hidden">' +
              '<div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-gradient-to-t from-black/60 to-transparent p-1.5"><span class="flex gap-1">' +
              '<button type="button" data-g-prev class="grid size-7 place-items-center rounded-lg bg-white/90 text-slate-700 hover:bg-white" aria-label="جابه‌جایی به عقب"><i class="ph ph-caret-right"></i></button>' +
              '<button type="button" data-g-next class="grid size-7 place-items-center rounded-lg bg-white/90 text-slate-700 hover:bg-white" aria-label="جابه‌جایی به جلو"><i class="ph ph-caret-left"></i></button></span>' +
              '<button type="button" data-g-remove class="grid size-7 place-items-center rounded-lg bg-coral-500 text-white hover:bg-coral-600" aria-label="حذف"><i class="ph ph-trash"></i></button></div>';
            d.querySelector('img').src = f.url;
            const h = d.querySelector('input'); h.name = box.dataset.name; h.value = f.id ?? f.path;
            grid.insertBefore(d, drop);
        }
        async function upload(list) {
            list = [...list].filter(f => /^image\//.test(f.type));
            const room = max - tiles().length;
            if (list.length > room) { adminToast('حداکثر ' + max + ' تصویر مجاز است.', 'warning'); list = list.slice(0, Math.max(room, 0)); }
            if (!list.length) return;
            const fd = new FormData(); list.forEach(f => fd.append('images[]', f));
            Object.entries(extra).forEach(([k, v]) => fd.append(k, v));
            txt.textContent = 'در حال آپلود…'; icon.className = 'ph ph-spinner-gap animate-spin text-2xl text-aron-500';
            try {
                const r = await adminFetch(box.dataset.upload, { method: 'POST', body: fd });
                const j = await r.json().catch(() => ({}));
                if (!r.ok) throw new Error((j.errors && Object.values(j.errors)[0][0]) || j.message || 'خطا در آپلود تصویر');
                j.files.forEach(tile);
            } catch (e) { adminToast(e.message, 'error'); }
            txt.innerHTML = idleHtml; icon.className = 'ph ph-images text-2xl text-aron-500'; file.value = '';
        }
        file.addEventListener('change', () => upload(file.files));
        ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { if (e.dataTransfer.types.includes('Files')) { e.preventDefault(); drop.classList.add('border-aron-400'); } }));
        ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, () => drop.classList.remove('border-aron-400')));
        drop.addEventListener('drop', e => { if (e.dataTransfer.files.length) { e.preventDefault(); upload(e.dataTransfer.files); } });

        grid.addEventListener('click', e => {
            const t = e.target.closest('[data-g-tile]'); if (!t) return;
            if (e.target.closest('[data-g-remove]')) t.remove();
            else if (e.target.closest('[data-g-prev]')) { const p = t.previousElementSibling; if (p && p.hasAttribute('data-g-tile')) grid.insertBefore(t, p); }
            else if (e.target.closest('[data-g-next]')) { const n = t.nextElementSibling; if (n && n.hasAttribute('data-g-tile')) grid.insertBefore(n, t); }
        });
        // drag-sort (HTML5 DnD)
        let dragging = null;
        grid.addEventListener('dragstart', e => { const t = e.target.closest('[data-g-tile]'); if (!t) return; dragging = t; t.classList.add('opacity-40'); e.dataTransfer.effectAllowed = 'move'; try { e.dataTransfer.setData('text/plain', 'tile'); } catch (_) {} });
        grid.addEventListener('dragend', () => { dragging?.classList.remove('opacity-40'); dragging = null; });
        grid.addEventListener('dragover', e => {
            if (!dragging) return; e.preventDefault();
            const over = e.target.closest('[data-g-tile]'); if (!over || over === dragging) return;
            const r = over.getBoundingClientRect(), rtl = getComputedStyle(grid).direction === 'rtl';
            const before = rtl ? e.clientX > r.left + r.width / 2 : e.clientX < r.left + r.width / 2;
            grid.insertBefore(dragging, before ? over : over.nextElementSibling);
        });
    }
    const run = () => document.querySelectorAll('[data-gallery]').forEach(init);
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', run) : run();
})();
</script>
@endonce
