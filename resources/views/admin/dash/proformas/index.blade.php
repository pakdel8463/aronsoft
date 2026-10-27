<x-admin.layout title="پیش‌فاکتورها" subtitle="صدور و پیگیری پیش‌فاکتور مشتریان" :breadcrumbs="[['label' => 'پیش‌فاکتورها']]">
    <x-slot:actions><x-admin.button :href="route('panel.proformas.create')" icon="plus">پیش‌فاکتور جدید</x-admin.button></x-slot:actions>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($statuses as $k => $l)
            <x-admin.stat-card :label="$l" :value="number_format($agg[$k]->c ?? 0)" :hint="number_format($agg[$k]->t ?? 0).' تومان'"
                :icon="['draft' => 'file-dashed', 'sent' => 'paper-plane-tilt', 'accepted' => 'handshake', 'paid' => 'seal-check'][$k]"
                :color="['draft' => 'violet', 'sent' => 'amber', 'accepted' => 'sky', 'paid' => 'mint'][$k]"
                :href="route('panel.proformas.index', ['status' => $k])" />
        @endforeach
    </div>

    <x-admin.card :padding="false">
        <form method="GET" class="flex flex-wrap items-end gap-3 p-4 sm:p-5">
            <div class="relative min-w-56 flex-1"><i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="عنوان پروژه، مشتری، ایمیل یا موبایل…" class="a-input ps-10"></div>
            <select name="status" class="a-select w-full sm:w-44"><option value="">همه وضعیت‌ها</option>@foreach ($statuses as $k => $l)<option value="{{ $k }}" @selected($filters['status'] === $k)>{{ $l }}</option>@endforeach</select>
            <x-admin.button type="submit" variant="soft" icon="funnel">اعمال</x-admin.button>
            @if (array_filter($filters))<x-admin.button :href="route('panel.proformas.index')" variant="ghost" icon="x">پاک‌کردن</x-admin.button>@endif
        </form>
        @if ($proformas->isEmpty())
            <x-admin.empty-state icon="receipt" title="پیش‌فاکتوری پیدا نشد" description="اولین پیش‌فاکتور را بسازید و لینک آن را برای مشتری بفرستید.">
                <x-admin.button :href="route('panel.proformas.create')" icon="plus">پیش‌فاکتور جدید</x-admin.button></x-admin.empty-state>
        @else
            <div class="hidden md:block">
                <x-admin.table flush class="px-4 pb-2">
                    <x-slot:head><x-admin.th>شماره</x-admin.th><x-admin.th>پروژه / مشتری</x-admin.th><x-admin.th>مبلغ (تومان)</x-admin.th><x-admin.th>وضعیت</x-admin.th><x-admin.th>تاریخ</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
                    @foreach ($proformas as $p)
                        <tr>
                            <x-admin.td muted>#{{ $p->id + 1000 }}</x-admin.td>
                            <x-admin.td><a href="{{ route('panel.proformas.edit', $p) }}" class="font-bold text-ink hover:text-aron-600">{{ $p->project_title }}</a>
                                <div class="text-xs text-slate-400">{{ $p->client_name }} · <span dir="ltr">{{ $p->client_phone ?: $p->client_email }}</span></div></x-admin.td>
                            <x-admin.td class="font-extrabold">{{ number_format($p->total_amount) }}</x-admin.td>
                            <x-admin.td><x-admin.badge :color="$tones[$p->status] ?? 'slate'" dot>{{ $statuses[$p->status] ?? $p->status }}</x-admin.badge></x-admin.td>
                            <x-admin.td muted nowrap>{{ jdate($p->created_at)->format('Y/m/d') }}</x-admin.td>
                            <x-admin.td class="text-end" nowrap>
                                <x-admin.button type="button" variant="ghost" size="sm" icon="link" icon-only title="کپی لینک" aria-label="کپی لینک" :data-copy="$p->publicUrl()" />
                                <x-admin.button :href="$p->publicUrl()" variant="ghost" size="sm" icon="eye" icon-only title="مشاهده" aria-label="مشاهده" target="_blank" rel="noopener" />
                                <x-admin.button :href="route('panel.proformas.edit', $p)" variant="ghost" size="sm" icon="pencil-simple" icon-only title="ویرایش" aria-label="ویرایش" />
                                <x-admin.confirm-delete :action="route('panel.proformas.destroy', $p)" icon-only message="این پیش‌فاکتور حذف شود؟ لینک عمومی آن هم از کار می‌افتد." />
                            </x-admin.td>
                        </tr>
                    @endforeach
                </x-admin.table>
            </div>
            <div class="divide-y divide-line/70 md:hidden">
                @foreach ($proformas as $p)
                    <div class="px-4 py-3.5">
                        <div class="flex items-center justify-between gap-2"><a href="{{ route('panel.proformas.edit', $p) }}" class="font-bold text-ink">{{ $p->project_title }}</a><x-admin.badge :color="$tones[$p->status] ?? 'slate'" dot>{{ $statuses[$p->status] ?? $p->status }}</x-admin.badge></div>
                        <div class="mt-1 text-xs text-slate-500">{{ $p->client_name }} · #{{ $p->id + 1000 }}</div>
                        <div class="mt-2 flex items-center justify-between"><span class="font-extrabold text-ink">{{ number_format($p->total_amount) }} <span class="text-xs font-medium text-slate-400">تومان</span></span>
                            <span class="flex gap-1"><x-admin.button type="button" variant="ghost" size="sm" icon="link" icon-only aria-label="کپی لینک" :data-copy="$p->publicUrl()" />
                                <x-admin.button :href="route('panel.proformas.edit', $p)" variant="soft" size="sm" icon="pencil-simple">ویرایش</x-admin.button></span></div>
                    </div>
                @endforeach
            </div>
        @endif
        <x-slot:footer>{{ $proformas->links('vendor.pagination.admin') }}</x-slot:footer>
    </x-admin.card>
    <x-slot:scripts><script>
        document.addEventListener('click', async (e) => { const c = e.target.closest('[data-copy]'); if (!c) return;
            try { await navigator.clipboard.writeText(c.dataset.copy); adminToast('لینک کپی شد.', 'success'); } catch (err) { window.prompt('لینک:', c.dataset.copy); } });
    </script></x-slot:scripts>
</x-admin.layout>
