@php($tabs = ['' => ['همه', $counts['all']], 'pending' => ['پیگیری نشده', $counts['pending']], 'contacted' => ['تماس گرفته شد', $counts['contacted']]])
<x-admin.layout title="لیدها" subtitle="درخواست‌هایی که از فرم‌های سایت ثبت شده‌اند" :breadcrumbs="[['label' => 'لیدها']]">
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($tabs as $key => [$label, $n])
            <a href="{{ route('panel.leads.index', array_filter(['status' => $key, 'q' => $filters['q'] ?? null, 'type' => $filters['type'] ?? null])) }}"
               class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-bold transition {{ ($filters['status'] ?? '') === $key ? 'bg-aron-600 text-white shadow-adm-brand' : 'bg-white text-slate-600 shadow-adm hover:text-aron-700' }}">
                {{ $label }}<span class="rounded-full bg-black/10 px-2 py-0.5 text-[11px]">{{ $n }}</span></a>
        @endforeach
    </div>

    <x-admin.card :padding="false">
        <form method="GET" class="flex flex-wrap items-end gap-3 p-4 sm:p-5">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
            <div class="relative min-w-56 flex-1">
                <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="نام، شرکت، ایمیل یا موبایل…" class="a-input ps-10">
            </div>
            @if ($types->isNotEmpty())
                <select name="type" class="a-select w-full sm:w-48"><option value="">همه نوع‌ها</option>
                    @foreach ($types as $t)<option value="{{ $t }}" @selected(($filters['type'] ?? '') === $t)>{{ $t }}</option>@endforeach</select>
            @endif
            <x-admin.button type="submit" variant="soft" icon="funnel">اعمال</x-admin.button>
            @if (array_filter($filters))<x-admin.button :href="route('panel.leads.index')" variant="ghost" icon="x">پاک‌کردن</x-admin.button>@endif
        </form>

        @if ($leads->isEmpty())
            <x-admin.empty-state icon="target" title="لیدی پیدا نشد" description="وقتی کسی فرم‌های سایت را پر کند، اینجا نمایش داده می‌شود." />
        @else
            <div class="hidden md:block">
                <x-admin.table flush class="px-4 pb-2">
                    <x-slot:head><x-admin.th>نام</x-admin.th><x-admin.th>تماس</x-admin.th><x-admin.th>نوع درخواست</x-admin.th><x-admin.th>وضعیت</x-admin.th><x-admin.th>تاریخ</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
                    @foreach ($leads as $l)
                        <tr>
                            <x-admin.td>
                                <button type="button" data-lead="{{ route('panel.leads.json', $l) }}" class="flex items-center gap-3 text-start">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-aron-100 text-sm font-extrabold text-aron-700">{{ mb_substr($l->full_name ?: '؟', 0, 1) }}</span>
                                    <span class="min-w-0"><span class="block truncate font-bold text-ink">{{ $l->full_name ?: '—' }}</span><span class="block truncate text-xs text-slate-400">{{ $l->company }}</span></span>
                                </button>
                            </x-admin.td>
                            <x-admin.td><div dir="ltr" class="text-end">{{ $l->mobile ?: '—' }}</div><div dir="ltr" class="text-end text-xs text-slate-400">{{ $l->email }}</div></x-admin.td>
                            <x-admin.td>{{ $l->request_type ?: '—' }}</x-admin.td>
                            <x-admin.td>
                                <form method="POST" action="{{ route('panel.leads.status', $l) }}">@csrf
                                    <input type="hidden" name="is_contacted" value="{{ $l->is_contacted ? 0 : 1 }}">
                                    <button type="submit" title="برای تغییر وضعیت کلیک کنید">
                                        @if ($l->is_contacted)<x-admin.badge color="mint" dot>تماس گرفته شد</x-admin.badge>@else<x-admin.badge color="amber" dot>نیاز به پیگیری</x-admin.badge>@endif
                                    </button>
                                </form>
                            </x-admin.td>
                            <x-admin.td muted nowrap>{{ jdate($l->created_at)->format('Y/m/d H:i') }}</x-admin.td>
                            <x-admin.td class="text-end" nowrap>
                                <x-admin.button type="button" variant="ghost" size="sm" icon="eye" icon-only title="مشاهده سریع" aria-label="مشاهده سریع" :data-lead="route('panel.leads.json', $l)" />
                                <x-admin.confirm-delete :action="route('panel.leads.destroy', $l)" icon-only message="این لید حذف شود؟" />
                            </x-admin.td>
                        </tr>
                    @endforeach
                </x-admin.table>
            </div>
            <div class="divide-y divide-line/70 md:hidden">
                @foreach ($leads as $l)
                    <button type="button" data-lead="{{ route('panel.leads.json', $l) }}" class="block w-full px-4 py-3.5 text-start">
                        <div class="flex items-center justify-between gap-2"><span class="font-bold text-ink">{{ $l->full_name ?: '—' }}</span>
                            @if ($l->is_contacted)<x-admin.badge color="mint" dot>تماس گرفته شد</x-admin.badge>@else<x-admin.badge color="amber" dot>پیگیری</x-admin.badge>@endif</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $l->request_type ?: 'بدون نوع' }} · <span dir="ltr">{{ $l->mobile }}</span></div>
                        <div class="mt-0.5 text-[11px] text-slate-400">{{ jdate($l->created_at)->format('Y/m/d H:i') }}</div>
                    </button>
                @endforeach
            </div>
        @endif
        <x-slot:footer>{{ $leads->links('vendor.pagination.admin') }}</x-slot:footer>
    </x-admin.card>

    {{-- کشوی جزئیات --}}
    <div id="lead-drawer" class="pointer-events-none fixed inset-0 z-[90]" aria-hidden="true">
        <div data-d-close class="absolute inset-0 bg-rail/50 opacity-0 transition-opacity duration-200"></div>
        <aside class="absolute inset-y-0 end-0 flex w-full max-w-md translate-x-[var(--dx)] flex-col bg-white shadow-adm-pop transition-transform duration-200" style="--dx:-100%" role="dialog" aria-label="جزئیات لید">
            <header class="flex items-center justify-between border-b border-line px-5 py-4"><h3 class="text-base font-extrabold text-ink">جزئیات لید</h3>
                <button type="button" data-d-close class="grid size-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100" aria-label="بستن"><i class="ph ph-x text-lg"></i></button></header>
            <div class="min-h-0 flex-1 overflow-y-auto p-5" data-d-body></div>
            <footer class="flex flex-wrap gap-2 border-t border-line bg-slate-50/70 px-5 py-3.5" data-d-foot></footer>
        </aside>
    </div>

    <x-slot:scripts>
        <script>
        (function () {
            const d = document.getElementById('lead-drawer'), panel = d.querySelector('aside'), back = d.querySelector('[data-d-close]'), body = d.querySelector('[data-d-body]'), foot = d.querySelector('[data-d-foot]');
            const rtl = document.dir === 'rtl' || document.documentElement.dir === 'rtl';
            const hidden = rtl ? '-100%' : '100%';
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const open = () => { d.classList.remove('pointer-events-none'); d.setAttribute('aria-hidden', 'false'); panel.style.setProperty('--dx', '0'); back.classList.remove('opacity-0'); };
            const close = () => { d.classList.add('pointer-events-none'); d.setAttribute('aria-hidden', 'true'); panel.style.setProperty('--dx', hidden); back.classList.add('opacity-0'); };
            panel.style.setProperty('--dx', hidden);
            d.querySelectorAll('[data-d-close]').forEach((b) => b.addEventListener('click', close));
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
            const row = (l, v, ltr) => v ? `<div class="flex items-start justify-between gap-4 border-b border-line/70 py-2.5 last:border-0"><dt class="shrink-0 text-xs text-slate-500">${l}</dt><dd class="min-w-0 break-words text-sm font-bold text-ink" ${ltr ? 'dir="ltr"' : ''}>${esc(v)}</dd></div>` : '';
            document.addEventListener('click', async (e) => {
                const t = e.target.closest('[data-lead]'); if (!t) return;
                body.innerHTML = '<div class="py-16 text-center text-sm text-slate-400">در حال بارگذاری…</div>'; foot.innerHTML = ''; open();
                try {
                    const r = await adminFetch(t.dataset.lead); const l = await r.json();
                    body.innerHTML = `<dl>${row('نام', l.name)}${row('شرکت', l.company)}${row('موبایل', l.mobile, 1)}${row('ایمیل', l.email, 1)}${row('نوع درخواست', l.request_type)}${row('تاریخ ثبت', l.date)}${row('صفحه فرم', l.page, 1)}${row('ارجاع‌دهنده', l.referrer, 1)}${row('منبع', l.utm, 1)}${row('IP', l.ip, 1)}</dl>
                      ${l.message ? `<div class="mt-4"><div class="mb-1.5 text-xs font-bold text-slate-500">پیام</div><p class="whitespace-pre-line rounded-2xl bg-slate-50 p-4 text-sm leading-7 text-slate-700">${esc(l.message)}</p></div>` : ''}
                      <form method="POST" action="${l.urls.note}" class="mt-4"><input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}"><input type="hidden" name="_method" value="PATCH">
                        <label class="a-label">یادداشت داخلی</label><textarea name="admin_note" rows="3" maxlength="5000" class="a-textarea" placeholder="نتیجه تماس…">${esc(l.admin_note)}</textarea>
                        <button class="a-btn a-btn-soft a-btn-sm mt-2"><i class="ph ph-floppy-disk"></i>ذخیره یادداشت</button></form>`;
                    foot.innerHTML = `<form method="POST" action="${l.urls.status}"><input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}"><input type="hidden" name="is_contacted" value="${l.is_contacted ? 0 : 1}">
                        <button class="a-btn ${l.is_contacted ? 'a-btn-secondary' : 'a-btn-success'}"><i class="ph ph-${l.is_contacted ? 'arrow-counter-clockwise' : 'check'}"></i>${l.is_contacted ? 'نیاز به پیگیری' : 'تماس گرفته شد'}</button></form>
                        ${l.mobile ? `<a class="a-btn a-btn-secondary" href="tel:${esc(l.mobile)}"><i class="ph ph-phone"></i>تماس</a>` : ''}${l.email ? `<a class="a-btn a-btn-secondary" href="mailto:${esc(l.email)}"><i class="ph ph-envelope-simple"></i>ایمیل</a>` : ''}
                        <a class="a-btn a-btn-ghost" href="${l.urls.show}">صفحه کامل</a>`;
                } catch (err) { body.innerHTML = '<div class="py-16 text-center text-sm text-coral-600">بارگذاری ناموفق بود.</div>'; }
            });
        })();
        </script>
    </x-slot:scripts>
</x-admin.layout>
