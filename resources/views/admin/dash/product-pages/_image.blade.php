{{-- انتخاب تصویر از کتابخانه‌ی رسانه. پارامترها: name, id, value, path (کلید خطا), label, hint, key (data-pp-f), size (sm|md) --}}
@php
    $value = (string) ($value ?? '');
    $src = \App\Support\ProductPageSchema::imageUrl($value);
    $size = $size ?? 'md';
@endphp
<x-admin.field :label="$label ?? null" :name="$path" :hint="$hint ?? null" :for="$id.'_btn'">
    <div class="flex items-center gap-3" data-pp-image>
        <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @if (! empty($key)) data-pp-f="{{ $key }}" @endif>
        <div class="relative grid shrink-0 place-items-center overflow-hidden rounded-2xl border border-line bg-slate-50 {{ $size === 'sm' ? 'size-16' : 'size-20' }}">
            <img id="{{ $id }}_prev" src="{{ $src }}" alt="" class="{{ $src ? '' : 'hidden' }} size-full object-contain" loading="lazy">
            <i data-pp-ph class="ph ph-image text-2xl text-slate-300 {{ $src ? 'hidden' : '' }}"></i>
        </div>
        <div class="flex min-w-0 flex-col items-start gap-1.5">
            <button type="button" id="{{ $id }}_btn" class="a-btn a-btn-secondary a-btn-sm" data-media-pick data-type="image" data-target="#{{ $id }}" data-preview="#{{ $id }}_prev">
                <i class="ph ph-image text-[1.15em]"></i>انتخاب از کتابخانه
            </button>
            <button type="button" class="a-btn a-btn-ghost a-btn-sm {{ $src ? '' : 'hidden' }}" data-pp-clear data-media-clear data-target="#{{ $id }}" data-preview="#{{ $id }}_prev">
                <i class="ph ph-x text-[1.15em]"></i>حذف تصویر
            </button>
        </div>
    </div>
</x-admin.field>
