@php use App\Helpers\Fa; @endphp
<x-admin.layout title="برچسب‌ها" subtitle="برچسب‌های نوشته‌ها؛ هنگام نوشتن هم می‌توانید برچسب جدید بسازید" :breadcrumbs="[['label' => 'نوشته‌ها', 'url' => route('panel.posts.index')], ['label' => 'برچسب‌ها']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.categories.index')" variant="secondary" icon="folders">دسته‌بندی‌ها</x-admin.button>
    </x-slot:actions>

    <div class="grid items-start gap-5 lg:grid-cols-[20rem_minmax(0,1fr)]">
        <x-admin.card title="برچسب جدید" icon="tag" id="tag-create">
            <form method="POST" action="{{ route('panel.tags.store') }}" class="space-y-4">
                @csrf
                <x-admin.input name="name" label="نام" required maxlength="80" />
                <x-admin.input name="slug" label="پیوند یکتا" hint="خالی = خودکار" dir="auto" />
                <x-admin.textarea name="description" label="توضیحات (اختیاری)" rows="3" />
                <x-admin.button type="submit" icon="plus" loading class="w-full">افزودن برچسب</x-admin.button>
            </form>
        </x-admin.card>

        <div class="space-y-4">
        <x-admin.card>
            <form method="GET" class="flex flex-wrap gap-2">
                <div class="relative min-w-[12rem] flex-1"><i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i><input type="search" name="q" value="{{ $q }}" placeholder="جستجوی برچسب…" class="a-input ps-10"></div>
                <select name="sort" class="a-select !w-auto" onchange="this.form.submit()"><option value="name" @selected($sort === 'name')>الفبا</option><option value="count" @selected($sort === 'count')>بیشترین نوشته</option><option value="new" @selected($sort === 'new')>جدیدترین</option></select>
                <x-admin.button type="submit" variant="secondary" icon="magnifying-glass">جستجو</x-admin.button>
            </form>
        </x-admin.card>
        <form method="POST" action="{{ route('panel.tags.bulk') }}">
            @csrf
            <x-admin.card :padding="false">
                <div class="flex flex-wrap items-center gap-2 border-b border-line px-4 py-3 sm:px-6">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-500"><input type="checkbox" class="a-check" id="tags-all">همه</label>
                    <x-admin.button type="submit" variant="danger-soft" size="sm" icon="trash" onclick="return confirm('برچسب‌های انتخاب‌شده حذف شوند؟ از نوشته‌ها جدا می‌شوند ولی نوشته‌ها حذف نمی‌شوند.')">حذف انتخاب‌شده‌ها</x-admin.button>
                </div>
                <x-admin.table flush>
                    <x-slot:head>
                        <x-admin.th class="w-10"></x-admin.th><x-admin.th>برچسب</x-admin.th><x-admin.th class="hidden sm:table-cell">نوشته‌ها</x-admin.th><x-admin.th class="text-end">عملیات</x-admin.th>
                    </x-slot:head>
                    @forelse ($tags as $t)
                        @php $tagEdit = ['id' => $t->id, 'name' => $t->name, 'slug' => $t->slug, 'description' => $t->description, 'action' => route('panel.tags.update', $t)]; @endphp
                        <tr>
                            <x-admin.td><input type="checkbox" name="ids[]" value="{{ $t->id }}" class="a-check tag-check"></x-admin.td>
                            <x-admin.td>
                                <a href="{{ route('panel.posts.index', ['tag_id' => $t->id]) }}" class="font-extrabold text-ink hover:text-aron-700">#{{ $t->name }}</a>
                                <p class="text-[11px] text-slate-400" dir="ltr" style="text-align:start">/{{ $t->slug }}</p>
                                @if ($t->description)<p class="mt-0.5 line-clamp-1 text-xs text-slate-400">{{ $t->description }}</p>@endif
                            </x-admin.td>
                            <x-admin.td class="hidden sm:table-cell"><x-admin.badge color="brand">{{ Fa::toFa($t->posts_count) }}</x-admin.badge></x-admin.td>
                            <x-admin.td class="text-end" nowrap>
                                <x-admin.button type="button" variant="soft" size="sm" icon="pencil-simple" icon-only title="ویرایش" data-tag-edit='@json($tagEdit)' />
                                <button type="submit" formaction="{{ route('panel.tags.destroy', $t) }}" formmethod="POST" name="_method" value="DELETE" class="a-btn a-btn-danger-soft a-btn-sm a-btn-icon" title="حذف" onclick="return confirm('این برچسب حذف شود؟')"><i class="ph ph-trash text-[1.15em]"></i></button>
                            </x-admin.td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-admin.empty-state icon="tag" title="برچسبی پیدا نشد" description="با فرم کنار صفحه یا هنگام نوشتن نوشته، برچسب بسازید." /></td></tr>
                    @endforelse
                </x-admin.table>
                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-slate-400">{{ Fa::toFa($tags->total()) }} برچسب</span>
                        <x-admin.pagination :paginator="$tags" class="!mt-0 w-full sm:w-auto" />
                    </div>
                </x-slot:footer>
            </x-admin.card>
        </form>
        </div>
    </div>

    <x-admin.modal name="tag-edit" title="ویرایش برچسب" max-width="md">
        <form method="POST" id="tag-edit-form" class="space-y-4">
            @csrf @method('PUT')
            <x-admin.input name="name" id="te-name" label="نام" required maxlength="80" />
            <x-admin.input name="slug" id="te-slug" label="پیوند یکتا" dir="auto" />
            <x-admin.textarea name="description" id="te-desc" label="توضیحات" rows="3" />
        </form>
        <x-slot:footer>
            <x-admin.button type="button" variant="ghost" data-modal-close>انصراف</x-admin.button>
            <x-admin.button type="submit" icon="check" form="tag-edit-form">ذخیره</x-admin.button>
        </x-slot:footer>
    </x-admin.modal>

    <x-slot:scripts>
        <script>
        document.getElementById('tags-all')?.addEventListener('change', (e) => document.querySelectorAll('.tag-check').forEach((c) => { c.checked = e.target.checked; }));
        document.addEventListener('click', (e) => {
            const b = e.target.closest('[data-tag-edit]');
            if (!b) return;
            const d = JSON.parse(b.dataset.tagEdit);
            const f = document.getElementById('tag-edit-form');
            f.action = d.action; document.getElementById('te-name').value = d.name; document.getElementById('te-slug').value = d.slug; document.getElementById('te-desc').value = d.description || '';
            adminModal.open('tag-edit');
        });
        </script>
    </x-slot:scripts>
</x-admin.layout>
