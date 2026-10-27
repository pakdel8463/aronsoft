@php
    use App\Helpers\Fa;
    use App\Models\Ticket;
    $fa = fn ($v) => Fa::toFa($v);
    $q = fn (array $over = []) => route('panel.tickets.index', array_filter(array_merge(request()->except('page'), $over), fn ($v) => $v !== null && $v !== '' && $v !== false));
    $tabIcons = ['open' => 'tray', 'staff' => 'headset', 'customer' => 'hourglass-medium', 'closed' => 'check-circle', 'all' => 'stack'];
@endphp
<x-admin.layout title="تیکت‌ها" subtitle="مدیریت درخواست‌های پشتیبانی مشتریان" :breadcrumbs="[['label' => 'تیکت‌ها']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.tickets.canned')" variant="secondary" icon="chat-text">پاسخ‌های آماده</x-admin.button>
        <x-admin.button :href="route('panel.tickets.departments')" variant="secondary" icon="folders">دپارتمان‌ها</x-admin.button>
    </x-slot:actions>

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-admin.stat-card label="منتظر پاسخ پشتیبان" :value="$fa($counts['staff'])" icon="headset" color="coral" :href="$q(['tab' => 'staff', 'sla' => null])" />
        <x-admin.stat-card label="خوانده‌نشده" :value="$fa($counts['unread'])" icon="envelope-simple" color="sky" />
        <x-admin.stat-card label="تأخیر در SLA" :value="$fa($counts['overdue'])" icon="alarm" color="amber" hint="بدون پاسخ اول در مهلت" :href="$q(['tab' => 'open', 'sla' => 1])" />
        <x-admin.stat-card label="ثبت‌شده امروز" :value="$fa($counts['today'])" icon="plus-circle" color="mint" />
    </div>

    <div class="mt-5 no-scrollbar flex gap-1 overflow-x-auto rounded-2xl bg-white p-1.5 shadow-adm" role="tablist">
        @foreach ($tabs as $key => $label)
            <a href="{{ $q(['tab' => $key]) }}" role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $tab === $key ? 'bg-aron-600 text-white shadow-adm-brand' : 'text-slate-500 hover:bg-aron-50 hover:text-aron-700' }}">
                <i class="ph ph-{{ $tabIcons[$key] }} text-base"></i>{{ $label }}
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold {{ $tab === $key ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }}">{{ $fa($counts[$key]) }}</span>
            </a>
        @endforeach
    </div>

    <x-admin.card class="mt-5">
        <form method="GET" action="{{ route('panel.tickets.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="sm:col-span-2"><label class="a-label" for="f-q">جستجو</label><input id="f-q" name="q" value="{{ $filters['q'] }}" class="a-input" placeholder="شماره، موضوع، نام یا ایمیل"></div>
            <div><label class="a-label" for="f-d">دپارتمان</label>
                <select id="f-d" name="department" class="a-select"><option value="">همه</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected($filters['department'] == $d->id)>{{ $d->name }}</option>@endforeach</select></div>
            <div><label class="a-label" for="f-p">اولویت</label>
                <select id="f-p" name="priority" class="a-select"><option value="">همه</option>@foreach(Ticket::PRIORITY_LABELS as $k => $l)<option value="{{ $k }}" @selected($filters['priority'] === $k)>{{ $l }}</option>@endforeach</select></div>
            <div><label class="a-label" for="f-a">مسئول</label>
                <select id="f-a" name="assignee" class="a-select"><option value="">همه</option><option value="me" @selected($filters['assignee'] === 'me')>من</option><option value="none" @selected($filters['assignee'] === 'none')>بدون مسئول</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}" @selected($filters['assignee'] === (string) $s->id)>{{ $s->name }}</option>@endforeach</select></div>
            <div class="flex items-end"><label class="flex min-h-[44px] cursor-pointer items-center gap-2 text-sm font-bold text-slate-700"><input type="checkbox" name="sla" value="1" class="a-check" @checked($filters['sla'])> فقط دارای تأخیر</label></div>
            <div><label class="a-label" for="f-f">از تاریخ</label><input id="f-f" name="from" value="{{ $filters['from'] }}" dir="ltr" class="a-input" placeholder="1405/07/01"></div>
            <div><label class="a-label" for="f-t">تا تاریخ</label><input id="f-t" name="to" value="{{ $filters['to'] }}" dir="ltr" class="a-input" placeholder="1405/07/30"></div>
            <div class="flex items-end gap-2 sm:col-span-2">
                <x-admin.button type="submit" icon="funnel">اعمال فیلتر</x-admin.button>
                <x-admin.button :href="route('panel.tickets.index', ['tab' => $tab])" variant="ghost">پاک‌کردن</x-admin.button>
            </div>
        </form>
    </x-admin.card>

    <form method="POST" action="{{ route('panel.tickets.bulk') }}" id="bulk-form" class="mt-5">
        @csrf
        <x-admin.card :padding="false">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2" id="bulk-bar">
                    <span class="text-xs font-bold text-slate-500"><span id="bulk-count">۰</span> انتخاب‌شده</span>
                    <select name="assigned_to" class="a-select !w-auto !py-1.5 text-sm" aria-label="ارجاع به"><option value="">ارجاع به…</option>@foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                    <x-admin.button type="submit" name="action" value="assign" size="sm" variant="soft" icon="user-switch">ارجاع</x-admin.button>
                    <x-admin.button type="submit" name="action" value="close" size="sm" variant="danger-soft" icon="x-circle" data-bulk-close>بستن</x-admin.button>
                </div>
            </x-slot:actions>
            <x-admin.table flush>
                <x-slot:head>
                    <x-admin.th><input type="checkbox" class="a-check" id="bulk-all" aria-label="انتخاب همه"></x-admin.th>
                    <x-admin.th>تیکت</x-admin.th><x-admin.th>مشتری</x-admin.th><x-admin.th>وضعیت</x-admin.th><x-admin.th>اولویت</x-admin.th><x-admin.th>SLA</x-admin.th><x-admin.th>مسئول</x-admin.th><x-admin.th>آخرین پیام</x-admin.th>
                </x-slot:head>
                @forelse($tickets as $t)
                    @php $sla = $t->sla(); @endphp
                    <tr class="{{ $t->staff_unread && ! $t->isClosed() ? 'bg-aron-50/40' : '' }}">
                        <x-admin.td><input type="checkbox" class="a-check bulk-item" name="ids[]" value="{{ $t->id }}" aria-label="انتخاب {{ $t->number }}"></x-admin.td>
                        <x-admin.td>
                            <a href="{{ route('panel.tickets.show', $t) }}" class="block max-w-[22rem] truncate font-extrabold text-ink hover:text-aron-600">
                                @if($t->staff_unread && ! $t->isClosed())<span class="me-1 inline-block size-2 rounded-full bg-coral-500 align-middle" title="جدید"></span>@endif{{ $t->subject }}</a>
                            <span class="mt-0.5 block text-xs text-slate-400"><span dir="ltr">{{ $t->number }}</span> · {{ $t->department?->name ?? '—' }}</span>
                        </x-admin.td>
                        <x-admin.td><span class="block font-bold">{{ $t->user?->name }}</span><span class="block text-xs text-slate-400" dir="ltr">{{ $t->user?->email }}</span></x-admin.td>
                        <x-admin.td nowrap><x-admin.badge :color="$t->status_color" dot>{{ $t->status_label }}</x-admin.badge></x-admin.td>
                        <x-admin.td nowrap><x-admin.badge :color="$t->priority_color">{{ $t->priority_label }}</x-admin.badge></x-admin.td>
                        <x-admin.td nowrap>@if($sla)<x-admin.badge :color="$sla['color']" icon="alarm">{{ $sla['label'] }}</x-admin.badge>@else<span class="text-slate-300">—</span>@endif</x-admin.td>
                        <x-admin.td nowrap>{{ $t->assignee?->name ?? '—' }}</x-admin.td>
                        <x-admin.td nowrap muted><span title="{{ Ticket::jdate($t->last_reply_at) }}">{{ Ticket::ago($t->last_reply_at) }}</span><span class="block text-[11px]">{{ $t->last_reply_by === 'staff' ? 'پشتیبان' : 'مشتری' }}</span></x-admin.td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-admin.empty-state icon="lifebuoy" title="تیکتی پیدا نشد" description="با تغییر تب یا فیلترها دوباره امتحان کنید." /></td></tr>
                @endforelse
            </x-admin.table>
            <x-slot:footer><x-admin.pagination :paginator="$tickets" class="!mt-0" /></x-slot:footer>
        </x-admin.card>
    </form>

    <x-slot:scripts>
        <script>
            (function () {
                var all = document.getElementById('bulk-all'), items = [].slice.call(document.querySelectorAll('.bulk-item')), cnt = document.getElementById('bulk-count');
                var fa = function (n) { return String(n).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; }); };
                function sync() { cnt.textContent = fa(items.filter(function (i) { return i.checked; }).length); }
                if (all) all.addEventListener('change', function () { items.forEach(function (i) { i.checked = all.checked; }); sync(); });
                items.forEach(function (i) { i.addEventListener('change', sync); });
                var f = document.getElementById('bulk-form');
                f.addEventListener('submit', function (e) {
                    var b = e.submitter;
                    if (!items.some(function (i) { return i.checked; })) { e.preventDefault(); adminToast('ابتدا حداقل یک تیکت را انتخاب کنید.', 'warning'); return; }
                    if (b && b.hasAttribute('data-bulk-close') && !confirm('تیکت‌های انتخاب‌شده بسته شوند؟')) e.preventDefault();
                });
            })();
        </script>
    </x-slot:scripts>
</x-admin.layout>
