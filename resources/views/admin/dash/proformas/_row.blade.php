<tr data-row="{{ $i }}">
    <td class="align-top">
        <input type="text" name="items[{{ $i }}][title]" data-title value="{{ $r['title'] ?? '' }}" class="a-input mb-2" placeholder="عنوان خدمت" required maxlength="255">
        <input type="text" name="items[{{ $i }}][description]" data-desc value="{{ $r['description'] ?? '' }}" class="a-input !h-9 text-xs" placeholder="توضیحات تکمیلی (اختیاری)" maxlength="500">
    </td>
    <td class="align-top"><input type="number" name="items[{{ $i }}][price]" data-price value="{{ $r['price'] ?? '' }}" class="a-input" dir="ltr" placeholder="0" min="0" step="1" required></td>
    <td class="whitespace-nowrap align-top text-end">
        <button type="button" data-move="up" class="a-btn a-btn-ghost a-btn-sm a-btn-icon" aria-label="بالا"><i class="ph ph-arrow-up"></i></button>
        <button type="button" data-move="down" class="a-btn a-btn-ghost a-btn-sm a-btn-icon" aria-label="پایین"><i class="ph ph-arrow-down"></i></button>
        <button type="button" data-del-row class="a-btn a-btn-danger-soft a-btn-sm a-btn-icon" aria-label="حذف ردیف"><i class="ph ph-trash"></i></button>
    </td>
</tr>
