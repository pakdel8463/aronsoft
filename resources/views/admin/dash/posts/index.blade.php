@php
    use App\Helpers\Fa;
    $tabs = ['all' => 'همه', 'published' => 'منتشرشده', 'scheduled' => 'زمان‌بندی‌شده', 'draft' => 'پیش‌نویس', 'private' => 'خصوصی', 'trash' => 'سطل زباله'];
    $filtered = request()->hasAny(['q', 'category_id', 'author_id', 'tag_id', 'from', 'to']);
    $statusMap = [
        'published' => ['منتشرشده', 'mint'],
        'scheduled' => ['زمان‌بندی‌شده', 'sky'],
        'draft' => ['پیش‌نویس', 'slate'],
        'private' => ['خصوصی', 'amber'],
    ];
    $qs = fn (array $over) => route('panel.posts.index', array_filter(array_merge(request()->except('page'), $over), fn ($v) => $v !== null && $v !== ''));
@endphp
<x-admin.layout title="نوشته‌ها" subtitle="مدیریت مطالب وبلاگ" :breadcrumbs="[['label' => 'نوشته‌ها']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.categories.index')" variant="secondary" icon="folders">دسته‌بندی‌ها</x-admin.button>
        <x-admin.button :href="route('panel.tags.index')" variant="secondary" icon="tag">برچسب‌ها</x-admin.button>
        <x-admin.button :href="route('panel.posts.create')" icon="plus">نوشته جدید</x-admin.button>
    </x-slot:actions>

    {{-- تب‌های وضعیت --}}
    <div class="no-scrollbar mb-4 flex gap-1 overflow-x-auto rounded-2xl bg-white p-1.5 shadow-adm">
        @foreach ($tabs as $key => $label)
            <a href="{{ $qs(['status' => $key === 'all' ? null : $key]) }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $tab === $key ? 'bg-aron-600 text-white shadow-adm-brand' : 'text-slate-500 hover:bg-aron-50 hover:text-aron-700' }}">
                {{ $label }}
                <span class="rounded-full px-1.5 text-[11px] leading-5 {{ $tab === $key ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }}">{{ Fa::toFa($counts[$key]) }}</span>
            </a>
        @endforeach
    </div>

    {{-- فیلترها --}}
    <x-admin.card class="mb-4">
        <form method="GET" action="{{ route('panel.posts.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12">
            @if ($tab !== 'all')<input type="hidden" name="status" value="{{ $tab }}">@endif
            <div class="relative sm:col-span-2 lg:col-span-4">
                <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="جستجو در عنوان، پیوند، خلاصه و متن…" class="a-input ps-10">
            </div>
            <div class="lg:col-span-2">
                <select name="category_id" class="a-select" aria-label="دسته‌بندی">
                    <option value="">همه دسته‌ها</option>
                    @foreach ($categoryOptions as $c)<option value="{{ $c['id'] }}" @selected((string) request('category_id') === (string) $c['id'])>{{ str_repeat('— ', $c['depth']) }}{{ $c['name'] }}</option>@endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <select name="author_id" class="a-select" aria-label="نویسنده">
                    <option value="">همه نویسندگان</option>
                    @foreach ($authors as $a)<option value="{{ $a->id }}" @selected((string) request('author_id') === (string) $a->id)>{{ $a->name }}</option>@endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <select name="tag_id" class="a-select" aria-label="برچسب">
                    <option value="">همه برچسب‌ها</option>
                    @foreach ($tags as $t)<option value="{{ $t->id }}" @selected((string) request('tag_id') === (string) $t->id)>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <select name="sort" class="a-select" aria-label="مرتب‌سازی">
                    @foreach ($sorts as $k => $l)<option value="{{ $k }}" @selected($sort === $k)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 lg:col-span-3"><span class="shrink-0 text-xs font-bold text-slate-500">از</span><input type="date" name="from" value="{{ request('from') }}" class="a-input" dir="ltr"></label>
            <label class="flex items-center gap-2 lg:col-span-3"><span class="shrink-0 text-xs font-bold text-slate-500">تا</span><input type="date" name="to" value="{{ request('to') }}" class="a-input" dir="ltr"></label>
            <div class="flex gap-2 sm:col-span-2 lg:col-span-6 lg:justify-end">
                <x-admin.button type="submit" icon="funnel">اعمال فیلتر</x-admin.button>
                @if ($filtered || $sort !== 'new')<x-admin.button :href="route('panel.posts.index', array_filter(['status' => $tab === 'all' ? null : $tab]))" variant="ghost" icon="x">پاک‌کردن</x-admin.button>@endif
            </div>
        </form>
    </x-admin.card>

    {{-- لیست + عملیات گروهی --}}
    <form method="POST" action="{{ route('panel.posts.bulk') }}" id="bulk-form" data-bulk-form>
        @csrf
        <x-admin.card :padding="false">
            <div class="flex flex-wrap items-center gap-2 border-b border-line px-4 py-3 sm:px-6" id="bulk-bar">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-500"><input type="checkbox" class="a-check" data-check-all aria-label="انتخاب همه">انتخاب همه</label>
                <span class="hidden text-xs font-bold text-aron-700" data-selected-count></span>
                <div class="ms-auto flex flex-wrap items-center gap-2">
                    <select name="action" class="a-select !h-9 !w-auto !text-xs" data-bulk-action aria-label="عملیات گروهی">
                        <option value="">عملیات گروهی…</option>
                        @if ($tab === 'trash')
                            <option value="restore">بازیابی</option>
                            <option value="delete">حذف برای همیشه</option>
                        @else
                            <option value="publish">انتشار</option>
                            <option value="draft">تبدیل به پیش‌نویس</option>
                            <option value="trash">انتقال به سطل زباله</option>
                            <option value="delete">حذف برای همیشه</option>
                        @endif
                    </select>
                    <x-admin.button type="submit" variant="secondary" size="sm" data-bulk-apply disabled>اعمال</x-admin.button>
                </div>
            </div>

            <x-admin.table flush>
                <x-slot:head>
                    <x-admin.th class="w-10"></x-admin.th>
                    <x-admin.th>نوشته</x-admin.th>
                    <x-admin.th class="hidden lg:table-cell">دسته‌بندی‌ها</x-admin.th>
                    <x-admin.th class="hidden md:table-cell">نویسنده</x-admin.th>
                    <x-admin.th class="hidden sm:table-cell">دیدگاه</x-admin.th>
                    <x-admin.th class="hidden md:table-cell">تاریخ</x-admin.th>
                    <x-admin.th class="text-end">عملیات</x-admin.th>
                </x-slot:head>

                @forelse ($posts as $p)
                    @php
                        $ds = $p->trashed() ? ['حذف‌شده', 'coral'] : ($statusMap[$p->display_status] ?? ['—', 'slate']);
                        $payload = [
                            'id' => $p->id, 'title' => $p->title, 'slug' => $p->slug, 'status' => $p->display_status === 'scheduled' ? 'scheduled' : $p->status,
                            'published_at' => $p->published_at?->format('Y-m-d\TH:i'), 'commentable' => (bool) ($p->commentable ?? true),
                            'categories' => $p->categories->pluck('id'), 'tags' => $p->tags->pluck('name'),
                        ];
                        $date = $p->published_at ?? $p->created_at;
                    @endphp
                    <tr data-row data-post='@json($payload)'>
                        <x-admin.td><input type="checkbox" name="ids[]" value="{{ $p->id }}" class="a-check" data-row-check aria-label="انتخاب"></x-admin.td>
                        <x-admin.td>
                            <div class="flex min-w-0 items-center gap-3">
                                @if ($p->featured_url)
                                    <img src="{{ $p->featured_url }}" alt="" loading="lazy" class="size-12 shrink-0 rounded-xl border border-line object-cover">
                                @else
                                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-300"><i class="ph ph-image text-xl"></i></span>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('panel.posts.edit', $p->id) }}" class="line-clamp-1 font-extrabold text-ink hover:text-aron-700">{{ $p->title }}</a>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <x-admin.badge :color="$ds[1]" dot>{{ $ds[0] }}</x-admin.badge>
                                        @if ($p->password)<x-admin.badge color="amber" icon="lock-simple">رمزدار</x-admin.badge>@endif
                                        @if ($p->noindex)<x-admin.badge color="slate" icon="eye-slash">noindex</x-admin.badge>@endif
                                        <span class="max-w-[14rem] truncate text-[11px] text-slate-400" dir="ltr">/{{ $p->slug }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-admin.td>
                        <x-admin.td class="hidden lg:table-cell">
                            <div class="flex max-w-[16rem] flex-wrap gap-1">
                                @forelse ($p->categories as $c)<span class="rounded-full bg-aron-50 px-2 py-0.5 text-[11px] font-bold text-aron-700">{{ $c->name }}</span>@empty<span class="text-xs text-slate-300">—</span>@endforelse
                            </div>
                        </x-admin.td>
                        <x-admin.td class="hidden md:table-cell" muted>{{ $p->author?->name ?? '—' }}</x-admin.td>
                        <x-admin.td class="hidden sm:table-cell">
                            @if ($p->comments_count)
                                <a href="{{ route('panel.comments.index', ['post_id' => $p->id, 'status' => 'pending']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 hover:text-aron-700"><i class="ph ph-chat-centered-text text-base"></i>{{ Fa::toFa($p->comments_count) }}</a>
                            @else<span class="text-xs text-slate-300">۰</span>@endif
                        </x-admin.td>
                        <x-admin.td class="hidden md:table-cell" nowrap>
                            <span class="block text-xs font-bold text-slate-600">{{ Fa::toFa(jdate($date)->format('Y/m/d')) }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $p->trashed() ? 'حذف: ' . Fa::toFa(jdate($p->deleted_at)->format('Y/m/d')) : Fa::toFa(jdate($date)->format('H:i')) }}</span>
                        </x-admin.td>
                        <x-admin.td class="text-end" nowrap>
                            <div class="inline-flex items-center gap-1">
                                @if (! $p->trashed())
                                    <x-admin.button :href="route('panel.posts.edit', $p->id)" variant="soft" size="sm" icon="pencil-simple" icon-only title="ویرایش" />
                                @endif
                                <x-admin.dropdown>
                                    <x-slot:trigger><x-admin.button variant="ghost" size="sm" icon="dots-three-vertical" icon-only title="بیشتر" /></x-slot:trigger>
                                    @if ($p->trashed())
                                        <x-admin.dropdown-item icon="arrow-counter-clockwise" data-post-action="{{ route('panel.posts.restore', $p->id) }}">بازیابی</x-admin.dropdown-item>
                                        <x-admin.dropdown-item icon="trash" :danger="true" data-post-action="{{ route('panel.posts.forceDelete', $p->id) }}" data-method="DELETE" data-confirm="این نوشته برای همیشه حذف شود؟ این کار قابل بازگشت نیست.">حذف برای همیشه</x-admin.dropdown-item>
                                    @else
                                        <x-admin.dropdown-item icon="lightning" data-quick-edit>ویرایش سریع</x-admin.dropdown-item>
                                        <x-admin.dropdown-item :href="$p->publicUrl()" icon="arrow-square-out" target="_blank" rel="noopener">{{ $p->isLive() ? 'مشاهده در سایت' : 'پیش‌نمایش' }}</x-admin.dropdown-item>
                                        <x-admin.dropdown-item icon="copy-simple" data-post-action="{{ route('panel.posts.duplicate', $p->id) }}">تکثیر</x-admin.dropdown-item>
                                        <x-admin.dropdown-item icon="trash" :danger="true" data-post-action="{{ route('panel.posts.destroy', $p->id) }}" data-method="DELETE" data-confirm="نوشته به سطل زباله منتقل شود؟">انتقال به سطل زباله</x-admin.dropdown-item>
                                    @endif
                                </x-admin.dropdown>
                            </div>
                        </x-admin.td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <x-admin.empty-state icon="article" :title="$filtered ? 'نوشته‌ای با این فیلتر پیدا نشد' : ($tab === 'trash' ? 'سطل زباله خالی است' : 'هنوز نوشته‌ای ثبت نشده')"
                                             :description="$filtered ? 'فیلترها را تغییر دهید یا پاک کنید.' : 'اولین نوشته‌ی وبلاگ را بنویسید.'">
                            @if (! $filtered && $tab !== 'trash')<x-admin.button :href="route('panel.posts.create')" icon="plus">نوشته جدید</x-admin.button>@endif
                        </x-admin.empty-state>
                    </td></tr>
                @endforelse
            </x-admin.table>

            <x-slot:footer>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-400">{{ Fa::toFa($posts->total()) }} نوشته</span>
                    <x-admin.pagination :paginator="$posts" class="!mt-0 w-full sm:w-auto" />
                </div>
            </x-slot:footer>
        </x-admin.card>
    </form>

    {{-- ویرایش سریع --}}
    <x-admin.modal name="quick-edit" title="ویرایش سریع" max-width="xl">
        <form id="quick-form" class="grid gap-4 sm:grid-cols-2" novalidate>
            <input type="hidden" name="id">
            <x-admin.input name="q_title" label="عنوان" class="sm:col-span-2" required />
            <x-admin.input name="q_slug" label="پیوند یکتا" dir="auto" />
            <x-admin.select name="q_status" label="وضعیت" :options="['draft' => 'پیش‌نویس', 'published' => 'منتشرشده', 'scheduled' => 'زمان‌بندی‌شده', 'private' => 'خصوصی']" />
            <div class="sm:col-span-2">
                <label class="a-label" for="q_published_at">زمان انتشار</label>
                <input type="datetime-local" id="q_published_at" class="a-input" dir="ltr">
            </div>
            <div class="sm:col-span-2">
                <p class="a-label">دسته‌بندی‌ها</p>
                <div class="grid max-h-36 gap-1 overflow-y-auto rounded-2xl border border-line bg-slate-50/60 p-2 sm:grid-cols-2" id="q-cats">
                    @foreach ($categoryOptions as $c)
                        <label class="flex items-center gap-2 rounded-lg px-2 py-1 text-sm font-semibold text-slate-700"><input type="checkbox" class="a-check" value="{{ $c['id'] }}">{{ str_repeat('— ', $c['depth']) }}{{ $c['name'] }}</label>
                    @endforeach
                </div>
            </div>
            <x-admin.input name="q_tags" label="برچسب‌ها" hint="با ویرگول جدا کنید" class="sm:col-span-2" />
            <label class="flex items-center gap-2 text-sm font-bold text-slate-700 sm:col-span-2"><input type="checkbox" class="a-switch" id="q_commentable">امکان ثبت دیدگاه</label>
            <p class="hidden text-xs font-semibold text-coral-600 sm:col-span-2" id="q-error"></p>
        </form>
        <x-slot:footer>
            <x-admin.button type="button" variant="ghost" data-modal-close>انصراف</x-admin.button>
            <x-admin.button type="button" icon="check" id="quick-save">ذخیره</x-admin.button>
        </x-slot:footer>
    </x-admin.modal>

    <x-slot:scripts>
        <script>
        (function () {
            const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
            const form = $('#bulk-form');
            const checks = () => $$('[data-row-check]', form);
            const sync = () => {
                const n = checks().filter((c) => c.checked).length;
                const el = $('[data-selected-count]', form);
                el.textContent = n ? n.toLocaleString('fa-IR') + ' مورد انتخاب شده' : '';
                el.classList.toggle('hidden', !n);
                $('[data-bulk-apply]', form).disabled = !(n && $('[data-bulk-action]', form).value);
                $('[data-check-all]', form).checked = n && n === checks().length;
                checks().forEach((c) => c.closest('tr').classList.toggle('bg-aron-50/50', c.checked));
            };
            form.addEventListener('change', (e) => {
                if (e.target.matches('[data-check-all]')) checks().forEach((c) => { c.checked = e.target.checked; });
                if (e.target.matches('[data-check-all],[data-row-check],[data-bulk-action]')) sync();
            });
            form.addEventListener('submit', (e) => {
                const a = $('[data-bulk-action]', form).value;
                if (!a) { e.preventDefault(); return; }
                const msg = { delete: 'نوشته‌های انتخاب‌شده برای همیشه حذف شوند؟ این کار قابل بازگشت نیست.', trash: 'نوشته‌های انتخاب‌شده به سطل زباله بروند؟' }[a];
                if (msg && !window.confirm(msg)) e.preventDefault();
            });

            // عملیات تکی (فرم موقت، چون داخل فرم گروهی nested form مجاز نیست)
            document.addEventListener('click', (e) => {
                const b = e.target.closest('[data-post-action]');
                if (!b) return;
                e.preventDefault();
                if (b.dataset.confirm && !window.confirm(b.dataset.confirm)) return;
                const f = document.createElement('form');
                f.method = 'POST'; f.action = b.dataset.postAction; f.style.display = 'none';
                f.innerHTML = '<input name="_token"><input name="_method">';
                f.elements._token.value = document.querySelector('meta[name="csrf-token"]').content;
                f.elements._method.value = b.dataset.method || 'POST';
                document.body.appendChild(f); f.submit();
            });

            // ویرایش سریع
            let cur = null;
            document.addEventListener('click', (e) => {
                const b = e.target.closest('[data-quick-edit]');
                if (!b) return;
                cur = JSON.parse(b.closest('[data-row]').dataset.post);
                const q = $('#quick-form');
                q.elements.id.value = cur.id; q.elements.q_title.value = cur.title; q.elements.q_slug.value = cur.slug; q.elements.q_status.value = cur.status;
                $('#q_published_at').value = cur.published_at || ''; $('#q_commentable').checked = !!cur.commentable;
                $$('#q-cats input').forEach((c) => { c.checked = cur.categories.includes(+c.value); });
                q.elements.q_tags.value = (cur.tags || []).join('، ');
                $('#q-error').classList.add('hidden');
                adminModal.open('quick-edit');
            });
            $('#quick-save').addEventListener('click', async (ev) => {
                const q = $('#quick-form'), btn = ev.currentTarget;
                btn.disabled = true;
                const body = {
                    id: cur.id, title: q.elements.q_title.value, slug: q.elements.q_slug.value, status: q.elements.q_status.value,
                    published_at: $('#q_published_at').value || null, commentable: $('#q_commentable').checked ? 1 : 0,
                    categories: $$('#q-cats input:checked').map((c) => +c.value), tags: q.elements.q_tags.value,
                };
                const r = await adminFetch(@json(route('panel.posts.quickUpdate')), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
                const j = await r.json().catch(() => ({}));
                btn.disabled = false;
                if (r.ok) { adminToast('تغییرات ذخیره شد'); setTimeout(() => location.reload(), 400); return; }
                const err = (j.errors && Object.values(j.errors)[0][0]) || j.message || 'ذخیره ناموفق بود';
                const el = $('#q-error'); el.textContent = err; el.classList.remove('hidden');
            });
        })();
        </script>
    </x-slot:scripts>
</x-admin.layout>
