@php
    use App\Helpers\Fa;
    $tabs = ['all' => 'همه', 'pending' => 'در انتظار', 'approved' => 'تأییدشده', 'rejected' => 'ردشده', 'spam' => 'اسپم'];
    $qs = fn (array $o) => route('panel.comments.index', array_filter(array_merge(request()->except('page', 'open'), $o), fn ($v) => $v !== null && $v !== '' && $v !== 'all'));
@endphp
<x-admin.layout title="دیدگاه‌ها" subtitle="بررسی، تأیید و پاسخ به نظرات بازدیدکنندگان" :breadcrumbs="[['label' => 'دیدگاه‌ها']]">
    <div class="no-scrollbar mb-4 flex gap-1 overflow-x-auto rounded-2xl bg-white p-1.5 shadow-adm">
        @foreach ($tabs as $key => $label)
            <a href="{{ $qs(['status' => $key]) }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $status === $key ? 'bg-aron-600 text-white shadow-adm-brand' : 'text-slate-500 hover:bg-aron-50 hover:text-aron-700' }}">
                {{ $label }}<span class="rounded-full px-1.5 text-[11px] leading-5 {{ $status === $key ? 'bg-white/20' : ($key === 'pending' && $counts['pending'] ? 'bg-coral-500 text-white' : 'bg-slate-100 text-slate-500') }}">{{ Fa::toFa($counts[$key]) }}</span>
            </a>
        @endforeach
    </div>

    <x-admin.card class="mb-4">
        <form method="GET" action="{{ route('panel.comments.index') }}" class="flex flex-wrap items-center gap-2">
            @if ($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
            @if ($postFilter)<input type="hidden" name="post_id" value="{{ $postFilter->id }}">@endif
            <div class="relative min-w-[12rem] flex-1">
                <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input type="search" name="q" value="{{ $q }}" class="a-input ps-10" placeholder="جستجو در متن، نام، ایمیل، IP یا عنوان نوشته…">
            </div>
            <x-admin.button type="submit" icon="magnifying-glass">جستجو</x-admin.button>
            @if ($postFilter)
                <a href="{{ $qs(['post_id' => null]) }}" class="inline-flex items-center gap-2 rounded-full bg-aron-50 px-3 py-2 text-xs font-bold text-aron-700">نوشته: {{ \Illuminate\Support\Str::limit($postFilter->title, 28) }}<i class="ph ph-x"></i></a>
            @endif
        </form>
    </x-admin.card>

    <form method="POST" action="{{ route('panel.comments.bulk') }}" id="c-bulk" data-comments>
        @csrf
        <div class="mb-3 flex flex-wrap items-center gap-2 rounded-2xl bg-white px-4 py-3 shadow-adm">
            <label class="flex items-center gap-2 text-xs font-bold text-slate-500"><input type="checkbox" class="a-check" id="c-all">انتخاب همه</label>
            <span class="hidden text-xs font-bold text-aron-700" id="c-count"></span>
            <div class="ms-auto flex flex-wrap items-center gap-2">
                <select name="action" class="a-select !h-9 !w-auto !text-xs" id="c-act" aria-label="عملیات گروهی">
                    <option value="">عملیات گروهی…</option>
                    <option value="approve">تأیید</option><option value="reject">رد</option><option value="spam">اسپم</option><option value="pending">برگشت به انتظار</option><option value="delete">حذف</option>
                </select>
                <x-admin.button type="submit" variant="secondary" size="sm" id="c-apply" disabled>اعمال</x-admin.button>
            </div>
        </div>

        <div class="space-y-3">
            @forelse ($comments as $c)
                @include('admin.dash.comments._item', ['c' => $c, 'depth' => 0, 'open' => $open])
                @if ($status === 'all' && ! empty($threads[$c->id]))
                    <div class="space-y-3 border-s-2 border-aron-100 ps-4 sm:ms-8 sm:ps-5">
                        @foreach ($threads[$c->id] as [$r, $d])
                            <div style="margin-inline-start: {{ max(0, $d - 1) * 1.25 }}rem">@include('admin.dash.comments._item', ['c' => $r, 'depth' => $d, 'open' => $open])</div>
                        @endforeach
                    </div>
                @endif
            @empty
                <x-admin.card><x-admin.empty-state icon="chat-centered-text" :title="$q || $status !== 'all' ? 'دیدگاهی پیدا نشد' : 'هنوز دیدگاهی ثبت نشده'" description="دیدگاه‌های ثبت‌شده در صفحه‌ی نوشته‌ها اینجا برای بررسی نمایش داده می‌شوند." /></x-admin.card>
            @endforelse
        </div>
        <x-admin.pagination :paginator="$comments" />
    </form>

    <x-slot:scripts>
        <script>
        (function () {
            const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const form = $('#c-bulk');
            const sync = () => {
                const n = $$('[data-c-check]:checked', form).length;
                $('#c-count').textContent = n ? n.toLocaleString('fa-IR') + ' مورد انتخاب شده' : ''; $('#c-count').classList.toggle('hidden', !n);
                $('#c-apply').disabled = !(n && $('#c-act').value);
            };
            form.addEventListener('change', (e) => {
                if (e.target.id === 'c-all') $$('[data-c-check]', form).forEach((c) => { c.checked = e.target.checked; });
                if (e.target.matches('#c-all,[data-c-check],#c-act')) sync();
            });
            form.addEventListener('submit', (e) => { if ($('#c-act').value === 'delete' && !window.confirm('دیدگاه‌های انتخاب‌شده (و پاسخ‌هایشان) حذف شوند؟')) e.preventDefault(); });

            const post = (url, method, fields) => {
                const f = document.createElement('form'); f.method = 'POST'; f.action = url; f.style.display = 'none';
                const add = (n, v) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v; f.appendChild(i); };
                add('_token', csrf); if (method && method !== 'POST') add('_method', method);
                Object.entries(fields || {}).forEach(([k, v]) => add(k, v));
                document.body.appendChild(f); f.submit();
            };
            document.addEventListener('click', (e) => {
                const a = e.target.closest('[data-c-action]');
                if (a) { if (a.dataset.confirm && !window.confirm(a.dataset.confirm)) return; post(a.dataset.cAction, a.dataset.method); return; }
                const r = e.target.closest('[data-c-reply]');
                if (r) { const box = $('[data-reply-box="' + r.dataset.cReply + '"]'); box.classList.toggle('hidden'); if (!box.classList.contains('hidden')) $('textarea', box).focus(); return; }
                const s = e.target.closest('[data-reply-send]');
                if (s) {
                    const box = s.closest('[data-reply-box]'); const t = $('[data-reply-text]', box).value.trim();
                    if (t.length < 2) { adminToast('متن پاسخ را بنویسید', 'warning'); return; }
                    s.disabled = true;
                    post(s.dataset.replySend, 'POST', { body: t, approve_parent: $('[data-reply-approve]', box)?.checked ? 1 : 0 });
                }
            });
            const open = {{ (int) $open }};
            if (open) $('#c' + open)?.scrollIntoView({ block: 'center' });
        })();
        </script>
    </x-slot:scripts>
</x-admin.layout>
