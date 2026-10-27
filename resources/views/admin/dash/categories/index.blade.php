@php
    use App\Helpers\Fa;
    $isPortfolio = $type === 'portfolio';
@endphp
<x-admin.layout title="دسته‌بندی‌ها" subtitle="درخت دسته‌بندی‌ها؛ با کشیدن یا فلش‌ها ترتیب را تغییر دهید" :breadcrumbs="[['label' => 'دسته‌بندی‌ها']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.tags.index')" variant="secondary" icon="tag">برچسب‌ها</x-admin.button>
        <x-admin.button :href="route('panel.categories.create', ['type' => $type])" icon="plus">دسته‌ی جدید</x-admin.button>
    </x-slot:actions>

    <div class="mb-4 flex gap-1 rounded-2xl bg-white p-1.5 shadow-adm sm:w-fit">
        @foreach (['blog' => ['وبلاگ', 'article'], 'portfolio' => ['نمونه‌کار', 'briefcase']] as $k => [$l, $ic])
            <a href="{{ route('panel.categories.index', ['type' => $k]) }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition sm:flex-none {{ $type === $k ? 'bg-aron-600 text-white shadow-adm-brand' : 'text-slate-500 hover:bg-aron-50 hover:text-aron-700' }}">
                <i class="ph ph-{{ $ic }} text-base"></i>{{ $l }}<span class="rounded-full px-1.5 text-[11px] leading-5 {{ $type === $k ? 'bg-white/20' : 'bg-slate-100' }}">{{ Fa::toFa($typeCounts[$k]) }}</span>
            </a>
        @endforeach
    </div>

    <x-admin.card :padding="false">
        <div id="cat-tree" data-reorder-url="{{ route('panel.categories.reorder') }}" data-toggle-url="{{ route('panel.categories.toggle', '__ID__') }}">
            @forelse ($tree as $row)
                @php($c = $row['model'])
                <div class="group flex items-center gap-2 border-b border-line/70 px-3 py-3 transition last:border-b-0 hover:bg-aron-50/30 sm:gap-3 sm:px-5"
                     data-cat data-id="{{ $c->id }}" data-parent="{{ $c->parent_id ?: 0 }}" data-depth="{{ $row['depth'] }}"
                     data-name="{{ $c->name }}" data-posts="{{ $c->posts_count }}" data-portfolios="{{ $c->portfolios_count }}"
                     data-action="{{ route('panel.categories.destroy', $c) }}" draggable="false">
                    <span class="hidden w-1 shrink-0 sm:block" style="width: {{ $row['depth'] * 1.5 }}rem"></span>
                    <span class="grid size-8 shrink-0 cursor-grab place-items-center rounded-lg text-slate-300 hover:bg-slate-100 hover:text-slate-500 active:cursor-grabbing" data-handle title="بکشید" ><i class="ph ph-dots-six-vertical text-lg"></i></span>
                    @if ($row['depth'])<i class="ph ph-arrow-elbow-down-left text-slate-300 sm:hidden"></i>@endif
                    <div class="flex min-w-0 flex-1 items-center gap-3" style="padding-inline-start: {{ $row['depth'] * 0.9 }}rem">
                        @if ($c->image)
                            <img src="{{ asset(ltrim($c->image, '/')) }}" alt="" class="size-10 shrink-0 rounded-xl border border-line object-cover">
                        @else
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-aron-50 text-aron-500"><i class="ph ph-{{ $row['depth'] ? 'folder-simple' : 'folder' }} text-xl"></i></span>
                        @endif
                        <div class="min-w-0">
                            <a href="{{ route('panel.categories.edit', $c) }}" class="block truncate text-sm font-extrabold text-ink hover:text-aron-700">{{ $c->name }}</a>
                            <p class="truncate text-[11px] text-slate-400" dir="ltr" style="text-align: start">/{{ $c->slug }}</p>
                        </div>
                    </div>
                    <span class="hidden shrink-0 text-xs font-bold text-slate-500 md:inline-flex md:items-center md:gap-1" title="{{ $isPortfolio ? 'نمونه‌کارها' : 'نوشته‌ها' }}"><i class="ph ph-{{ $isPortfolio ? 'briefcase' : 'article' }} text-base text-slate-400"></i>{{ Fa::toFa($isPortfolio ? $c->portfolios_count : $c->posts_count) }}</span>
                    <label class="hidden shrink-0 cursor-pointer sm:block" title="فعال / غیرفعال"><input type="checkbox" class="a-switch" data-toggle @checked($c->is_active)></label>
                    <div class="flex shrink-0 items-center gap-0.5">
                        <button type="button" data-move="up" class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:opacity-25" title="بالا" @disabled($row['first'])><i class="ph ph-arrow-up"></i></button>
                        <button type="button" data-move="down" class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:opacity-25" title="پایین" @disabled($row['last'])><i class="ph ph-arrow-down"></i></button>
                        <x-admin.dropdown>
                            <x-slot:trigger><x-admin.button variant="ghost" size="sm" icon="dots-three-vertical" icon-only title="بیشتر" /></x-slot:trigger>
                            <x-admin.dropdown-item :href="route('panel.categories.edit', $c)" icon="pencil-simple">ویرایش</x-admin.dropdown-item>
                            <x-admin.dropdown-item :href="route('panel.categories.create', ['type' => $type, 'parent' => $c->id])" icon="folder-simple-plus">افزودن زیرمجموعه</x-admin.dropdown-item>
                            @if (! $isPortfolio && $c->is_active)<x-admin.dropdown-item :href="route('blog.category', $c->slug)" target="_blank" icon="arrow-square-out">مشاهده در سایت</x-admin.dropdown-item>@endif
                            <x-admin.dropdown-item icon="trash" :danger="true" data-cat-delete>حذف…</x-admin.dropdown-item>
                        </x-admin.dropdown>
                    </div>
                </div>
            @empty
                <x-admin.empty-state icon="folders" title="هنوز دسته‌ای ساخته نشده" description="با دسته‌بندی، مطالب را مرتب و پیدا کردن آن‌ها را آسان‌تر کنید.">
                    <x-admin.button :href="route('panel.categories.create', ['type' => $type])" icon="plus">اولین دسته را بسازید</x-admin.button>
                </x-admin.empty-state>
            @endforelse
        </div>
    </x-admin.card>

    {{-- حذف امن --}}
    <x-admin.modal name="cat-delete" title="حذف دسته‌بندی" max-width="md">
        <form method="POST" id="cat-delete-form" class="space-y-4">
            @csrf @method('DELETE')
            <p class="text-sm leading-7 text-slate-600">دسته‌ی <strong id="cd-name" class="text-ink"></strong> حذف می‌شود. زیرمجموعه‌های آن به دسته‌ی والد منتقل می‌شوند.</p>
            <div id="cd-reassign" class="space-y-2">
                <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold leading-6 text-amber-700" id="cd-count"></p>
                <label class="a-label" for="cd-target">انتقال موارد به</label>
                <select name="reassign_to" id="cd-target" class="a-select"></select>
                <p class="text-xs text-slate-400" id="cd-hint"></p>
            </div>
        </form>
        <x-slot:footer>
            <x-admin.button type="button" variant="ghost" data-modal-close>انصراف</x-admin.button>
            <x-admin.button type="submit" variant="danger" icon="trash" form="cat-delete-form">حذف دسته</x-admin.button>
        </x-slot:footer>
    </x-admin.modal>

    <x-slot:scripts>
        <script>
        (function () {
            const tree = document.getElementById('cat-tree');
            if (!tree) return;
            const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
            const rows = () => $$('[data-cat]', tree);
            const isPortfolio = {{ $isPortfolio ? 'true' : 'false' }};

            /* بلوک یک دسته = خودش + همه‌ی نوادگانش (ردیف‌های پشت‌سرهم با عمق بیشتر) */
            const block = (row) => {
                const out = [row]; const d = +row.dataset.depth; let n = row.nextElementSibling;
                while (n && +n.dataset.depth > d) { out.push(n); n = n.nextElementSibling; }
                return out;
            };
            const siblings = (row) => rows().filter((r) => r.dataset.parent === row.dataset.parent);
            function refreshArrows() {
                rows().forEach((r) => {
                    const sib = siblings(r), i = sib.indexOf(r);
                    $('[data-move=up]', r).disabled = i === 0;
                    $('[data-move=down]', r).disabled = i === sib.length - 1;
                });
            }
            async function save(row) {
                const ids = siblings(row).map((r) => +r.dataset.id);
                const r = await adminFetch(tree.dataset.reorderUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ids }) });
                r.ok ? adminToast('ترتیب ذخیره شد') : adminToast('ذخیره‌ی ترتیب ناموفق بود', 'error');
            }
            function move(row, dir) {
                const sib = siblings(row), i = sib.indexOf(row);
                if (dir === 'up' && i > 0) block(row).forEach((n) => tree.insertBefore(n, sib[i - 1]));
                else if (dir === 'down' && i < sib.length - 1) { const after = block(sib[i + 1]); const ref = after[after.length - 1].nextElementSibling; block(row).forEach((n) => tree.insertBefore(n, ref)); }
                else return;
                refreshArrows(); save(row);
            }

            tree.addEventListener('click', (e) => {
                const m = e.target.closest('[data-move]');
                if (m) move(m.closest('[data-cat]'), m.dataset.move);
            });
            tree.addEventListener('change', async (e) => {
                if (!e.target.matches('[data-toggle]')) return;
                const row = e.target.closest('[data-cat]');
                const r = await adminFetch(tree.dataset.toggleUrl.replace('__ID__', row.dataset.id), { method: 'POST' });
                if (!r.ok) { e.target.checked = !e.target.checked; adminToast('تغییر وضعیت ناموفق بود', 'error'); } else adminToast(e.target.checked ? 'دسته فعال شد' : 'دسته غیرفعال شد');
            });

            /* کشیدن و رها کردن (فقط بین هم‌نیاها) */
            let drag = null;
            tree.addEventListener('mousedown', (e) => { const h = e.target.closest('[data-handle]'); if (h) h.closest('[data-cat]').draggable = true; });
            tree.addEventListener('dragstart', (e) => { const r = e.target.closest('[data-cat]'); if (!r) return; drag = r; r.classList.add('opacity-50'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', r.dataset.id); });
            tree.addEventListener('dragend', () => { if (drag) { drag.classList.remove('opacity-50'); drag.draggable = false; } $$('.ring-2', tree).forEach((x) => x.classList.remove('ring-2', 'ring-aron-300', 'ring-inset')); drag = null; });
            tree.addEventListener('dragover', (e) => {
                const r = e.target.closest('[data-cat]');
                if (!drag || !r || r === drag || r.dataset.parent !== drag.dataset.parent) return;
                e.preventDefault();
                $$('.ring-2', tree).forEach((x) => x.classList.remove('ring-2', 'ring-aron-300', 'ring-inset'));
                r.classList.add('ring-2', 'ring-aron-300', 'ring-inset');
            });
            tree.addEventListener('drop', (e) => {
                const r = e.target.closest('[data-cat]');
                if (!drag || !r || r === drag || r.dataset.parent !== drag.dataset.parent) return;
                e.preventDefault();
                const sib = siblings(drag), from = sib.indexOf(drag), to = sib.indexOf(r);
                if (from < to) { const b = block(r); const ref = b[b.length - 1].nextElementSibling; block(drag).forEach((n) => tree.insertBefore(n, ref)); }
                else block(drag).forEach((n) => tree.insertBefore(n, r));
                refreshArrows(); save(drag);
            });

            /* حذف امن */
            tree.addEventListener('click', (e) => {
                const b = e.target.closest('[data-cat-delete]');
                if (!b) return;
                const row = b.closest('[data-cat]');
                const forbidden = new Set(block(row).map((r) => r.dataset.id));
                const count = isPortfolio ? +row.dataset.portfolios : +row.dataset.posts;
                $('#cat-delete-form').action = row.dataset.action;
                $('#cd-name').textContent = row.dataset.name;
                const sel = $('#cd-target'); sel.innerHTML = '';
                if (!isPortfolio) sel.add(new Option('بدون انتقال — در صورت نداشتن دسته‌ی دیگر به «بدون دسته‌بندی» می‌رود', ''));
                else sel.add(new Option('بدون انتقال', ''));
                rows().filter((r) => !forbidden.has(r.dataset.id)).forEach((r) => sel.add(new Option('— '.repeat(+r.dataset.depth) + r.dataset.name, r.dataset.id)));
                $('#cd-reassign').classList.toggle('hidden', count === 0);
                $('#cd-count').textContent = count + ' ' + (isPortfolio ? 'نمونه‌کار' : 'نوشته') + ' در این دسته هست.';
                $('#cd-hint').textContent = isPortfolio ? '' : 'نوشته‌هایی که دسته‌ی دیگری ندارند هرگز بدون دسته نمی‌مانند.';
                adminModal.open('cat-delete');
            });
        })();
        </script>
    </x-slot:scripts>
</x-admin.layout>
