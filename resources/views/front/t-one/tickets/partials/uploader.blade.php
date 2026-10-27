{{-- انتخاب پیوست‌ها + نمایش نام فایل‌ها (JS ساده) --}}
<div>
    <label class="form-label" for="attachments">پیوست‌ها <span class="font-normal text-slate-500">(اختیاری)</span></label>
    <input type="file" id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.zip,.txt" data-max-files="{{ $rules['max_files'] }}" data-max-mb="{{ $rules['max_mb'] }}"
           class="form-input !py-2 file:me-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-tech-blue">
    <p class="form-help">حداکثر {{ \App\Helpers\Fa::toFa($rules['max_files']) }} فایل، هر کدام تا {{ \App\Helpers\Fa::toFa($rules['max_mb']) }} مگابایت — فرمت‌های مجاز: <span dir="ltr">{{ implode(', ', $rules['exts']) }}</span></p>
    @error('attachments')<p class="form-error">{{ $message }}</p>@enderror
    @foreach($errors->get('attachments.*') as $msgs)<p class="form-error">{{ $msgs[0] }}</p>@endforeach
    <p class="form-error hidden" id="att-error"></p>
</div>
<script>
    (function () {
        var i = document.getElementById('attachments'); if (!i) return;
        i.addEventListener('change', function () {
            var e = document.getElementById('att-error'), max = +i.dataset.maxFiles, mb = +i.dataset.maxMb, msg = '';
            if (i.files.length > max) msg = 'حداکثر ' + max + ' فایل مجاز است.';
            for (var k = 0; k < i.files.length; k++) if (i.files[k].size > mb * 1048576) msg = 'اندازهٔ «' + i.files[k].name + '» بیشتر از ' + mb + ' مگابایت است.';
            e.textContent = msg; e.classList.toggle('hidden', !msg);
            if (msg) i.value = '';
        });
    })();
</script>
