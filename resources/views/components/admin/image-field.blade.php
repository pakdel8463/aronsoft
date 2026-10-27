@props([
    'name' => 'featured_image',
    'value' => null,
    'label' => null,
    'hint' => null,
    'uploadUrl',
    'extra' => [],
])
{{-- تک‌تصویر با آپلود درجا (کشیدن‌و‌رها یا کلیک). مقدار ذخیره‌شده = مسیر نسبی/آدرس تصویر. Vanilla JS. --}}
@php
    $current = old(trim(str_replace(['[', ']'], ['.', ''], $name), '.'), $value);
    $src = $current ? (preg_match('#^(https?:)?//#', $current) ? $current : asset(ltrim($current, '/'))) : '';
    $uid = 'if_' . \Illuminate\Support\Str::random(6);
@endphp
<x-admin.field :label="$label" :name="$name" :hint="$hint" :for="$uid">
    <div data-image-field data-upload="{{ $uploadUrl }}" data-extra='@json($extra)' data-base="{{ asset('') }}">
        <input type="hidden" name="{{ $name }}" value="{{ $current }}" data-if-value>
        <div data-if-preview class="{{ $src ? '' : 'hidden' }} mb-3 overflow-hidden rounded-2xl border border-line bg-slate-50">
            <img data-if-img src="{{ $src }}" alt="" class="mx-auto max-h-56 w-full object-contain">
        </div>
        <label for="{{ $uid }}" data-if-drop
               class="flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 px-4 py-6 text-center text-sm text-slate-500 transition hover:border-aron-400 hover:bg-aron-50/50">
            <i class="ph ph-cloud-arrow-up text-2xl text-aron-500" data-if-icon></i>
            <span data-if-text class="font-semibold">تصویر را بکشید و رها کنید یا کلیک کنید</span>
            <span class="text-xs text-slate-400">jpg، png، webp — حداکثر ۴ مگابایت</span>
        </label>
        <input type="file" id="{{ $uid }}" accept="image/jpeg,image/png,image/webp" class="sr-only" data-if-file>
        <div class="mt-2 {{ $src ? '' : 'hidden' }}" data-if-actions>
            <x-admin.button type="button" variant="danger-soft" size="sm" icon="trash" data-if-remove>حذف تصویر</x-admin.button>
        </div>
    </div>
</x-admin.field>
@once
<script>
(function () {
    function init(box) {
        if (box.dataset.ready) return; box.dataset.ready = 1;
        const val = box.querySelector('[data-if-value]'), prev = box.querySelector('[data-if-preview]'),
              img = box.querySelector('[data-if-img]'), file = box.querySelector('[data-if-file]'),
              drop = box.querySelector('[data-if-drop]'), actions = box.querySelector('[data-if-actions]'),
              txt = box.querySelector('[data-if-text]'), icon = box.querySelector('[data-if-icon]');
        const extra = JSON.parse(box.dataset.extra || '{}');
        const show = (path, url) => {
            val.value = path || '';
            img.src = url || '';
            prev.classList.toggle('hidden', !path);
            actions.classList.toggle('hidden', !path);
        };
        async function upload(f) {
            if (!f) return;
            const fd = new FormData(); fd.append('images[]', f);
            Object.entries(extra).forEach(([k, v]) => fd.append(k, v));
            txt.textContent = 'در حال آپلود…'; icon.className = 'ph ph-spinner-gap animate-spin text-2xl text-aron-500';
            try {
                const r = await adminFetch(box.dataset.upload, { method: 'POST', body: fd });
                const j = await r.json().catch(() => ({}));
                if (!r.ok) throw new Error((j.errors && Object.values(j.errors)[0][0]) || j.message || 'خطا در آپلود تصویر');
                show(j.files[0].path, j.files[0].url);
            } catch (e) { adminToast(e.message, 'error'); }
            txt.textContent = 'تصویر را بکشید و رها کنید یا کلیک کنید'; icon.className = 'ph ph-cloud-arrow-up text-2xl text-aron-500';
            file.value = '';
        }
        file.addEventListener('change', () => upload(file.files[0]));
        box.querySelector('[data-if-remove]').addEventListener('click', () => show('', ''));
        ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('border-aron-400'); }));
        ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('border-aron-400'); }));
        drop.addEventListener('drop', e => upload(e.dataTransfer.files[0]));
    }
    const run = () => document.querySelectorAll('[data-image-field]').forEach(init);
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', run) : run();
})();
</script>
@endonce
