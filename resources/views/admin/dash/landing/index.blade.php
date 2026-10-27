<x-admin.layout title="صفحات لندینگ" subtitle="مدیریت وضعیت و سئوی صفحات فرود" :breadcrumbs="[['label' => 'صفحات لندینگ']]">
    <x-slot:actions><x-admin.button :href="route('panel.landing.create')" icon="plus">لندینگ جدید</x-admin.button></x-slot:actions>

    <div class="mb-5 rounded-2xl bg-amber-50 p-4 text-sm leading-7 text-amber-800">
        محتوای صفحات عمومی لندینگ داخل فایل‌های قالب تعریف شده و از دیتابیس خوانده نمی‌شود؛ رکوردهای این بخش فقط برای مدیریت، وضعیت و سئو هستند.
        @if ($codePages)
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($codePages as $c)
                    <a href="{{ $c['url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-aron-700" dir="ltr"><i class="ph ph-arrow-square-out"></i>{{ $c['slug'] }}</a>
                @endforeach
            </div>
        @endif
    </div>

    <x-admin.card :padding="false">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-line p-4 sm:p-5">
            <div class="min-w-[12rem] flex-1"><div class="relative"><i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i><input type="text" name="q" value="{{ $q }}" placeholder="جستجو…" aria-label="جستجو" class="a-input ps-10"></div></div>
            <x-admin.button type="submit" variant="soft" icon="funnel">اعمال</x-admin.button>
        </form>
        <x-admin.table flush>
            <x-slot:head><x-admin.th>عنوان</x-admin.th><x-admin.th>قالب</x-admin.th><x-admin.th>وضعیت</x-admin.th><x-admin.th>تاریخ</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
            @forelse ($landings as $l)
                <tr>
                    <x-admin.td>
                        <a href="{{ route('panel.landing.edit', $l) }}" class="block font-extrabold text-ink hover:text-aron-700">{{ $l->title }}</a>
                        <span class="block text-xs text-slate-400" dir="ltr">{{ $l->slug }}</span>
                    </x-admin.td>
                    <x-admin.td muted>{{ $templates[$l->template] ?? $l->template }}</x-admin.td>
                    <x-admin.td><x-admin.badge :color="$l->is_active ? 'mint' : 'slate'" dot>{{ $l->is_active ? 'فعال' : 'غیرفعال' }}</x-admin.badge></x-admin.td>
                    <x-admin.td muted nowrap>{{ \App\Helpers\Fa::toFa(jdate($l->created_at)->format('Y/m/d')) }}</x-admin.td>
                    <x-admin.td class="text-end" nowrap>
                        <div class="flex items-center justify-end gap-1">
                            <x-admin.button :href="route('panel.landing.edit', $l)" variant="soft" size="sm" icon="pencil-simple" icon-only title="ویرایش" aria-label="ویرایش" />
                            <x-admin.confirm-delete :action="route('panel.landing.destroy', $l)" icon-only size="sm" />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="browser" title="لندینگی ثبت نشده"><x-admin.button :href="route('panel.landing.create')" icon="plus">لندینگ جدید</x-admin.button></x-admin.empty-state></td></tr>
            @endforelse
        </x-admin.table>
        <x-slot:footer><x-admin.pagination :paginator="$landings" class="!mt-0" /></x-slot:footer>
    </x-admin.card>
</x-admin.layout>
