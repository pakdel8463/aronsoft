{{-- انتخاب آیکن (پاپ‌اور مشترک در form.blade.php). پارامترها: name, id, value, path, label, hint, key --}}
@php
    $value = in_array((string) ($value ?? ''), \App\Support\ProductPageSchema::icons(), true) ? (string) $value : '';
@endphp
<x-admin.field :label="$label ?? null" :name="$path" :hint="$hint ?? null" :for="$id.'_btn'">
    <div class="flex items-center gap-2" data-pp-icon>
        <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @if (! empty($key)) data-pp-f="{{ $key }}" @endif>
        <button type="button" id="{{ $id }}_btn" class="a-btn a-btn-secondary" data-pp-icon-btn aria-haspopup="dialog" aria-expanded="false">
            <span class="grid size-7 place-items-center rounded-lg bg-aron-50 text-lg text-aron-600"><i data-pp-icon-cur class="ph {{ $value ?: 'ph-plus' }}"></i></span>
            <span data-pp-icon-label dir="ltr" class="max-w-[8rem] truncate text-xs font-bold">{{ $value ? substr($value, 3) : 'انتخاب آیکن' }}</span>
        </button>
        <button type="button" class="a-btn a-btn-ghost a-btn-icon a-btn-sm {{ $value ? '' : 'hidden' }}" data-pp-icon-clear title="بدون آیکن" aria-label="بدون آیکن"><i class="ph ph-x"></i></button>
    </div>
</x-admin.field>
