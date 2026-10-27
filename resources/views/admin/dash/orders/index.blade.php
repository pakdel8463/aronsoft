@php
    $tabs = ['' => ['همه', $counts->sum(), 'slate']] + collect($statuses)->map(fn ($l, $k) => [$l, $counts[$k] ?? 0, \App\Models\Order::tone($k)])->all();
    $hasFilter = collect($filters)->filter()->isNotEmpty();
@endphp
<x-admin.layout title="سفارش‌ها" subtitle="درخواست‌های ثبت‌شده از فرم «ثبت سفارش» سایت" :breadcrumbs="[['label' => 'سفارش‌ها']]">
    <x-slot:actions><x-admin.button :href="route('panel.orders.export', $filters)" variant="secondary" icon="download-simple">خروجی CSV</x-admin.button></x-slot:actions>

    <div class="no-scrollbar mb-4 flex gap-2 overflow-x-auto pb-1">
        @foreach ($tabs as $key => [$label, $n])
            <a href="{{ route('panel.orders.index', array_filter(array_merge($filters, ['status' => $key]))) }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-2xl px-4 py-2 text-sm font-bold transition {{ ($filters['status'] ?? '') === $key ? 'bg-aron-600 text-white shadow-adm-brand' : 'bg-white text-slate-600 shadow-adm hover:text-aron-700' }}">
                {{ $label }}<span class="rounded-full bg-black/10 px-2 py-0.5 text-[11px]">{{ $n }}</span></a>
        @endforeach
    </div>

    <x-admin.card :padding="false">
        <form method="GET" class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-6 lg:items-end">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
            <div class="relative sm:col-span-2"><i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="کد، نام، موبایل، ایمیل…" class="a-input ps-10"></div>
            <select name="type" class="a-select" aria-label="نوع"><option value="">همه نوع‌ها</option>@foreach ($types as $k => $l)<option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $l }}</option>@endforeach</select>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="a-input" aria-label="از تاریخ" title="از تاریخ (میلادی)">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="a-input" aria-label="تا تاریخ" title="تا تاریخ (میلادی)">
            <div class="flex gap-2"><x-admin.button type="submit" variant="soft" icon="funnel" class="flex-1">اعمال</x-admin.button>
                @if ($hasFilter)<x-admin.button :href="route('panel.orders.index')" variant="ghost" icon="x" icon-only title="پاک‌کردن" aria-label="پاک‌کردن" />@endif</div>
        </form>

        @if ($orders->isEmpty())
            <x-admin.empty-state icon="shopping-bag" title="سفارشی پیدا نشد" description="سفارش‌های تازه از صفحه ثبت سفارش سایت اینجا نمایش داده می‌شود." />
        @else
            <div class="hidden md:block">
                <x-admin.table flush class="px-4 pb-2">
                    <x-slot:head><x-admin.th>کد</x-admin.th><x-admin.th>مشتری</x-admin.th><x-admin.th>نوع</x-admin.th><x-admin.th>بودجه</x-admin.th><x-admin.th>وضعیت</x-admin.th><x-admin.th>تاریخ</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
                    @foreach ($orders as $o)
                        <tr>
                            <x-admin.td><a href="{{ route('panel.orders.show', $o) }}" dir="ltr" class="font-extrabold text-aron-700">{{ $o->code }}</a></x-admin.td>
                            <x-admin.td><div class="font-bold text-ink">{{ $o->name }}</div><div dir="ltr" class="text-end text-xs text-slate-400">{{ $o->mobile }}</div></x-admin.td>
                            <x-admin.td>{{ $o->type_label }}</x-admin.td>
                            <x-admin.td muted>{{ $o->budget_label ?? '—' }}</x-admin.td>
                            <x-admin.td>
                                <x-admin.dropdown align="start" width="w-52">
                                    <x-slot:trigger><button type="button" class="rounded-full focus:outline-none focus:ring-4 focus:ring-aron-100" title="تغییر وضعیت"><x-admin.badge :color="$o->status_tone" dot>{{ $o->status_label }}</x-admin.badge></button></x-slot:trigger>
                                    @foreach ($statuses as $k => $l)
                                        <form method="POST" action="{{ route('panel.orders.status', $o) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $k }}">
                                            <x-admin.dropdown-item type="submit" :icon="$o->status === $k ? 'check' : null">{{ $l }}</x-admin.dropdown-item></form>
                                    @endforeach
                                </x-admin.dropdown>
                            </x-admin.td>
                            <x-admin.td muted nowrap>{{ jdate($o->created_at)->format('Y/m/d H:i') }}</x-admin.td>
                            <x-admin.td class="text-end"><x-admin.button :href="route('panel.orders.show', $o)" variant="soft" size="sm" icon="eye">مشاهده</x-admin.button></x-admin.td>
                        </tr>
                    @endforeach
                </x-admin.table>
            </div>
            <div class="divide-y divide-line/70 md:hidden">
                @foreach ($orders as $o)
                    <a href="{{ route('panel.orders.show', $o) }}" class="block px-4 py-3.5">
                        <div class="flex items-center justify-between gap-2"><span dir="ltr" class="font-extrabold text-aron-700">{{ $o->code }}</span><x-admin.badge :color="$o->status_tone" dot>{{ $o->status_label }}</x-admin.badge></div>
                        <div class="mt-1 font-bold text-ink">{{ $o->name }}</div>
                        <div class="mt-0.5 text-xs text-slate-500">{{ $o->type_label }} · {{ $o->budget_label ?? 'بودجه نامشخص' }}</div>
                        <div class="mt-0.5 text-[11px] text-slate-400">{{ jdate($o->created_at)->format('Y/m/d H:i') }}</div>
                    </a>
                @endforeach
            </div>
        @endif
        <x-slot:footer>{{ $orders->links('vendor.pagination.admin') }}</x-slot:footer>
    </x-admin.card>
</x-admin.layout>
