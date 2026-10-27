<x-admin.layout title="رسانه" subtitle="کتابخانه‌ی تصاویر، ویدیو، صوت و اسناد" :breadcrumbs="[['label' => 'رسانه']]">
    <x-slot:actions>
        <span class="hidden items-center gap-1.5 rounded-full bg-white px-3 py-2 text-xs font-bold text-slate-500 shadow-adm sm:inline-flex" title="دیسک ذخیره‌سازی (MEDIA_DISK)"><i class="ph ph-hard-drives text-base text-aron-500"></i>{{ $disk === 'public_uploads' ? 'public/uploads' : ($disk === 'public' ? 'storage/app/public/uploads' : $disk) }}</span>
    </x-slot:actions>

    <div data-media-library>
        <x-admin.card :padding="false">
            <div class="flex flex-col p-4 sm:p-5" style="height: max(30rem, calc(100vh - 15rem))">
                <div data-media-lib-host class="flex min-h-0 flex-1 flex-col"></div>
            </div>
            <x-slot:footer>
                <p class="text-xs leading-6 text-slate-500">
                    فایل‌ها را بکشید و رها کنید. تصاویر خودکار به WebP و اندازه‌های بندانگشتی/متوسط/بزرگ تبدیل می‌شوند.
                    حداکثر حجم: تصویر {{ number_format($maxKb['image'] / 1024, 0) }}، صوت {{ number_format($maxKb['audio'] / 1024, 0) }}، ویدیو {{ number_format($maxKb['video'] / 1024, 0) }}، سند {{ number_format($maxKb['file'] / 1024, 0) }} مگابایت (محدودیت سرور: {{ $maxUpload }}).
                    فرمت‌های اجرایی و SVG مجاز نیستند.
                </p>
            </x-slot:footer>
        </x-admin.card>

        {{-- نوار عملیات گروهی --}}
        <div class="fixed inset-x-0 bottom-4 z-40 hidden justify-center px-4" data-media-bulk>
            <div class="a-card flex items-center gap-3 !rounded-2xl px-4 py-3 shadow-adm-lift">
                <span class="text-sm font-extrabold text-ink" data-bulk-count></span>
                <button type="button" class="a-btn a-btn-danger a-btn-sm" data-act="bulk-delete"><i class="ph ph-trash"></i>حذف</button>
                <button type="button" class="a-btn a-btn-ghost a-btn-sm" data-act="bulk-clear">لغو انتخاب</button>
            </div>
        </div>

        {{-- کشوی جزئیات --}}
        <div class="fixed inset-0 z-[80] hidden bg-rail/50 backdrop-blur-[2px]" data-drawer-backdrop></div>
        <aside data-media-drawer aria-label="جزئیات فایل"
               class="pointer-events-none fixed inset-y-0 end-0 z-[85] flex w-full max-w-md -translate-x-full flex-col bg-white opacity-0 shadow-adm-pop transition duration-300">
            <header class="flex items-center justify-between border-b border-line px-5 py-3.5">
                <h3 class="text-base font-extrabold text-ink">جزئیات فایل</h3>
                <button type="button" data-drawer-close class="grid size-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100" aria-label="بستن"><i class="ph ph-x text-lg"></i></button>
            </header>
            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">
                <div data-f-preview></div>
                <div class="grid gap-3">
                    <label class="block"><span class="a-label">عنوان</span><input class="a-input" data-f="title" maxlength="255"></label>
                    <label class="block"><span class="a-label">متن جایگزین (alt)</span><input class="a-input" data-f="alt" maxlength="255" placeholder="برای تصاویر؛ مهم برای سئو"></label>
                    <label class="block"><span class="a-label">کپشن</span><textarea class="a-textarea !min-h-20" data-f="caption" maxlength="2000"></textarea></label>
                    <button type="button" class="a-btn a-btn-primary" data-act="save"><i class="ph ph-floppy-disk"></i>ذخیره تغییرات</button>
                </div>
                <div>
                    <p class="a-label">آدرس فایل</p>
                    <div class="flex gap-2"><input class="a-input !h-10 text-xs" dir="ltr" readonly data-f="url" onclick="this.select()"><button type="button" class="a-btn a-btn-secondary a-btn-sm shrink-0" data-act="copy-url"><i class="ph ph-copy"></i>کپی</button></div>
                    <div class="mt-2 flex flex-wrap gap-1.5"><button type="button" class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600 hover:bg-aron-50 hover:text-aron-700" data-act="copy-rel">کپی مسیر نسبی</button><span class="contents" data-f-variants></span></div>
                </div>
                <dl class="divide-y divide-line/70 rounded-2xl bg-slate-50/70 px-4 py-1" data-f-info></dl>
                <div><p class="a-label">محل استفاده</p><div data-f-usage></div></div>
            </div>
            <footer class="flex gap-2 border-t border-line bg-slate-50/70 p-4">
                <label class="a-btn a-btn-secondary flex-1 cursor-pointer"><i class="ph ph-arrows-clockwise"></i>جایگزینی فایل<input type="file" class="sr-only" data-act="replace"></label>
                <button type="button" class="a-btn a-btn-danger-soft" data-act="delete"><i class="ph ph-trash"></i>حذف</button>
            </footer>
        </aside>
    </div>

    <x-slot:scripts><x-admin.media-config /></x-slot:scripts>
</x-admin.layout>
