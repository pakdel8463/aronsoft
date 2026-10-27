<script>
(function () {
    const input = document.querySelector('[data-avatar-input]');
    const box = document.querySelector('[data-avatar-preview]');
    if (!input || !box) return;
    input.addEventListener('change', async () => {
        const f = input.files[0];
        if (!f) return;
        if (f.size > 2 * 1024 * 1024) { adminToast('حجم تصویر باید حداکثر ۲ مگابایت باشد.', 'error'); input.value = ''; return; }
        const url = URL.createObjectURL(f);
        box.innerHTML = '<img src="' + url + '" alt="" class="size-28 rounded-full object-cover ring-2 ring-white shadow-adm">';
        const up = input.dataset.uploadUrl;
        if (!up) return; // صفحه ساخت: فایل همراه فرم ارسال می‌شود
        const fd = new FormData(); fd.append('file', f);
        try {
            const r = await adminFetch(up, { method: 'POST', body: fd });
            const j = await r.json().catch(() => ({}));
            if (r.ok) { input.value = ''; adminToast('تصویر ذخیره شد.', 'success'); }
            else adminToast((j.errors && Object.values(j.errors)[0][0]) || j.message || 'آپلود ناموفق بود.', 'error');
        } catch (e) { adminToast('آپلود ناموفق بود.', 'error'); }
    });
})();
</script>
