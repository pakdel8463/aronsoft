<x-admin.layout title="دسترسی‌ها" subtitle="فهرست دسترسی‌های قابل تخصیص به نقش‌ها و کاربران" :breadcrumbs="[['label' => 'دسترسی‌ها']]">
    <x-slot:actions>
        @can('permission-create')
            <form method="POST" action="{{ route('panel.permissions.seed') }}" data-confirm="دسترسی‌های استاندارد پنل ساخته شود؟ موارد موجود تغییری نمی‌کنند.">@csrf
                <x-admin.button type="submit" variant="secondary" icon="magic-wand">ساخت دسترسی‌های استاندارد</x-admin.button></form>
            <x-admin.button :href="route('panel.permissions.create')" icon="plus">دسترسی جدید</x-admin.button>
        @endcan
    </x-slot:actions>

    @if ($total === 0)
        <x-admin.card><x-admin.empty-state icon="key" title="هنوز دسترسی‌ای ساخته نشده" description="با یک کلیک دسترسی‌های استاندارد پنل (کاربران، نقش‌ها، لیدها، منوها…) ساخته می‌شود." /></x-admin.card>
    @else
        <div class="mb-4 relative max-w-md">
            <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
            <input type="search" id="perm-search" placeholder="جستجو در نام یا شناسه…" class="a-input ps-10">
        </div>
        <div class="grid gap-5 xl:grid-cols-2" id="perm-groups">
            @foreach ($groups as $group => $perms)
                <x-admin.card :title="$group" :subtitle="$perms->count().' دسترسی'" icon="folders" :padding="false" data-perm-group>
                    <div class="divide-y divide-line/70">
                        @foreach ($perms as $p)
                            <div class="flex items-center gap-3 px-5 py-3 sm:px-6" data-perm-row data-q="{{ $p->name }} {{ $p->slug }}">
                                <div class="min-w-0 flex-1"><div class="truncate text-sm font-bold text-ink">{{ $p->name }}</div><div class="truncate text-xs text-slate-400" dir="ltr">{{ $p->slug }}</div></div>
                                <span class="hidden text-xs text-slate-400 sm:block">{{ $p->roles_count }} نقش · {{ $p->users_count }} کاربر</span>
                                @can('permission-edit')<x-admin.button :href="route('panel.permissions.edit', $p)" variant="ghost" size="sm" icon="pencil-simple" icon-only title="ویرایش" aria-label="ویرایش" />@endcan
                                @can('permission-delete')<x-admin.confirm-delete :action="route('panel.permissions.destroy', $p)" icon-only message="دسترسی «{{ $p->name }}» حذف شود؟ از همه نقش‌ها و کاربران برداشته می‌شود." />@endcan
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endforeach
        </div>
        <p id="perm-none" class="hidden py-10 text-center text-sm text-slate-400">موردی پیدا نشد.</p>
    @endif

    <x-slot:scripts>
        <script>
        (function () {
            const s = document.getElementById('perm-search'); if (!s) return;
            s.addEventListener('input', () => {
                const q = s.value.trim().toLowerCase(); let n = 0;
                document.querySelectorAll('[data-perm-group]').forEach((g) => {
                    let any = 0;
                    g.querySelectorAll('[data-perm-row]').forEach((r) => { const h = !q || r.dataset.q.toLowerCase().includes(q); r.classList.toggle('hidden', !h); if (h) any++; });
                    g.classList.toggle('hidden', !any); n += any;
                });
                document.getElementById('perm-none').classList.toggle('hidden', n > 0);
            });
        })();
        </script>
    </x-slot:scripts>
</x-admin.layout>
