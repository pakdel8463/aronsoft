{{-- پیش‌نمایش زنده‌ی صفحه در iframe مقیاس‌شده. ورودی: $url (+ #anchor اختیاری). JS: public/js/admin/page-builder.js --}}
<div class="a-card overflow-hidden" data-pb-preview>
    <div class="flex items-center justify-between gap-2 border-b border-line px-3 py-2">
        <span class="flex items-center gap-1.5 text-xs font-bold text-slate-500"><span class="size-2 animate-pulse rounded-full bg-mint-500"></span> پیش‌نمایش زنده</span>
        <div class="flex items-center gap-1" role="group" aria-label="اندازه‌ی نمایش">
            <button type="button" class="rounded-lg px-2.5 py-1 text-xs font-bold text-slate-500 hover:bg-slate-100 pb-pressed" data-pb-device="desktop" aria-pressed="true"><i class="ph ph-desktop"></i> دسکتاپ</button>
            <button type="button" class="rounded-lg px-2.5 py-1 text-xs font-bold text-slate-500 hover:bg-slate-100 pb-pressed" data-pb-device="mobile" aria-pressed="false"><i class="ph ph-device-mobile"></i> موبایل</button>
            <button type="button" class="grid size-7 place-items-center rounded-lg text-slate-500 hover:bg-slate-100" data-pb-reload title="بارگذاری دوباره" aria-label="بارگذاری دوباره"><i class="ph ph-arrows-clockwise"></i></button>
            <a href="{{ $url }}" target="_blank" class="grid size-7 place-items-center rounded-lg text-slate-500 hover:bg-slate-100" title="باز کردن در تب جدید" aria-label="باز کردن در تب جدید"><i class="ph ph-arrow-square-out"></i></a>
        </div>
    </div>
    <div class="relative overflow-hidden bg-slate-100" data-pb-box style="height:600px">
        <iframe data-pb-frame src="{{ $url }}" title="پیش‌نمایش صفحه" class="absolute top-0 border-0 bg-white" style="transform-origin:top right;right:0;width:1280px;height:600px"></iframe>
    </div>
</div>
