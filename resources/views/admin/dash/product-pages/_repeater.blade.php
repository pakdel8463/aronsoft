{{-- لیست قابل‌تکرار. ورودی: field, name, path, stored. ردیف‌ها سمت سرور از داده ساخته می‌شوند و یک <template> با __IDX__ برای ردیف جدید. --}}
@php
    $rows = (! ($tpl ?? false) && session()->hasOldInput()) ? (array) old($path, []) : (array) $stored;
    $rows = array_values(array_filter($rows, 'is_array'));
    $max = (int) ($field['max_items'] ?? 12);
    $labelKey = $field['item_label'] ?? ($field['fields'][0]['key'] ?? '');
    $rname = $name;
    $rpath = $path;
    $rfield = $field;
    $itemWord = $field['label'];
@endphp
<div class="min-w-0" data-pp-repeater data-name="{{ $rname }}" data-max="{{ $max }}" data-next="{{ count($rows) }}" data-label-key="{{ $labelKey }}">
    <div class="mb-2 flex items-center justify-between gap-2">
        <span class="a-label !mb-0">{{ $field['label'] }}</span>
        <span class="text-xs font-bold text-slate-400" data-pp-count aria-live="polite"></span>
    </div>
    <div class="space-y-3" data-pp-rows>
        @foreach ($rows as $i => $row)
            @include('admin.dash.product-pages._row', ['i' => $i, 'row' => $row, 'tpl' => false, 'field' => $rfield, 'name' => $rname, 'path' => $rpath])
        @endforeach
    </div>
    <p class="rounded-2xl border border-dashed border-line bg-slate-50/60 px-4 py-6 text-center text-sm text-slate-400" data-pp-empty>هنوز موردی اضافه نشده است.</p>
    @if ($errors->has($rpath))<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $errors->first($rpath) }}</p>@endif
    <template data-pp-tpl>
        @include('admin.dash.product-pages._row', ['i' => '__IDX__', 'row' => [], 'tpl' => true, 'field' => $rfield, 'name' => $rname, 'path' => $rpath])
    </template>
    <button type="button" class="a-btn a-btn-soft a-btn-sm mt-3" data-pp-add>
        <i class="ph ph-plus text-[1.15em]"></i>افزودن مورد
    </button>
</div>
