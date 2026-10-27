{{-- یک ردیف از repeater. ورودی: field(repeater), name, path, i, row, tpl --}}
@php
    $lk = $field['item_label'] ?? '';
    $rowTitle = $tpl ? '' : trim((string) ($row[$lk] ?? ''));
    $subFields = $field['fields'];
    $rowName = $name;
    $rowPath = $path;
    $rowField = $field;
    $rowTpl = $tpl;
    $rowIdx = $i;
    $rowData = $row;
@endphp
<div class="rounded-2xl border border-line bg-slate-50/50" data-pp-row>
    <div class="flex items-center gap-1.5 px-3 py-2">
        <button type="button" class="flex min-w-0 flex-1 items-center gap-2 rounded-lg py-1 text-start focus:outline-none focus-visible:ring-4 focus-visible:ring-aron-100" data-pp-row-toggle aria-expanded="true">
            <i class="ph ph-caret-down text-slate-400 transition" data-pp-caret></i>
            <span class="truncate text-sm font-extrabold text-ink" data-pp-row-title>{{ $rowTitle }}</span>
        </button>
        <button type="button" class="a-btn a-btn-ghost a-btn-icon a-btn-sm" data-pp-row-up title="انتقال به بالا" aria-label="انتقال به بالا"><i class="ph ph-arrow-up"></i></button>
        <button type="button" class="a-btn a-btn-ghost a-btn-icon a-btn-sm" data-pp-row-down title="انتقال به پایین" aria-label="انتقال به پایین"><i class="ph ph-arrow-down"></i></button>
        <button type="button" class="a-btn a-btn-ghost a-btn-icon a-btn-sm" data-pp-row-dup title="تکثیر" aria-label="تکثیر"><i class="ph ph-copy-simple"></i></button>
        <button type="button" class="a-btn a-btn-danger-soft a-btn-icon a-btn-sm" data-pp-row-del title="حذف" aria-label="حذف"><i class="ph ph-trash"></i></button>
    </div>
    <div class="grid gap-4 border-t border-line p-4 sm:grid-cols-2" data-pp-row-body>
        @foreach ($subFields as $sf)
            @php $wide = in_array($sf['type'], ['textarea', 'lines', 'repeater'], true) || ($sf['type'] === 'text' && ($sf['max'] ?? 0) >= 80); @endphp
            <div class="min-w-0 {{ $wide ? 'sm:col-span-2' : '' }}">
                @include('admin.dash.product-pages._field', [
                    'field' => $sf,
                    'name' => $rowName . '[' . $rowIdx . '][' . $sf['key'] . ']',
                    'path' => $rowPath . '.' . $rowIdx . '.' . $sf['key'],
                    'stored' => $rowData[$sf['key']] ?? null,
                    'tpl' => $rowTpl,
                ])
            </div>
        @endforeach
    </div>
</div>
