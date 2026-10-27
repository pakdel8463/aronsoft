<x-admin.layout title="منوها" subtitle="منوی هدر و ستون‌های فوتر وب‌سایت" :breadcrumbs="[['label' => 'منوها']]">
    <x-slot:actions><x-admin.button :href="route('panel.menus.create')" icon="plus">منوی جدید</x-admin.button></x-slot:actions>
    <x-admin.card :padding="false">
        @if ($menus->isEmpty())
            <x-admin.empty-state icon="list-bullets" title="هنوز منویی ساخته نشده" description="منو بسازید و آیتم‌های آن را با کشیدن و رها کردن بچینید.">
                <x-admin.button :href="route('panel.menus.create')" icon="plus">منوی جدید</x-admin.button>
            </x-admin.empty-state>
        @else
            <div class="hidden md:block">
                <x-admin.table class="pt-3">
                    <x-slot:head><x-admin.th>نام</x-admin.th><x-admin.th>موقعیت</x-admin.th><x-admin.th>نامک</x-admin.th><x-admin.th>آیتم‌ها</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
                    @foreach ($menus as $m)
                        <tr>
                            <x-admin.td><a href="{{ route('panel.menus.items', $m) }}" class="font-bold text-ink hover:text-aron-600">{{ $m->name }}</a></x-admin.td>
                            <x-admin.td><x-admin.badge color="brand">{{ $m->position_label }}</x-admin.badge></x-admin.td>
                            <x-admin.td muted><span dir="ltr">{{ $m->slug }}</span></x-admin.td>
                            <x-admin.td>{{ $m->items_count }}</x-admin.td>
                            <x-admin.td class="text-end" nowrap>
                                <x-admin.button :href="route('panel.menus.items', $m)" variant="soft" size="sm" icon="tree-structure">مدیریت آیتم‌ها</x-admin.button>
                                <x-admin.button :href="route('panel.menus.edit', $m)" variant="ghost" size="sm" icon="pencil-simple" icon-only title="ویرایش" aria-label="ویرایش" />
                                <x-admin.confirm-delete :action="route('panel.menus.destroy', $m)" icon-only message="منوی «{{ $m->name }}» حذف شود؟ این منو دیگر در سایت نمایش داده نمی‌شود." />
                            </x-admin.td>
                        </tr>
                    @endforeach
                </x-admin.table>
            </div>
            <div class="divide-y divide-line/70 md:hidden">
                @foreach ($menus as $m)
                    <div class="px-4 py-4">
                        <div class="flex items-center justify-between gap-2"><a href="{{ route('panel.menus.items', $m) }}" class="font-bold text-ink">{{ $m->name }}</a><x-admin.badge color="brand">{{ $m->position_label }}</x-admin.badge></div>
                        <div class="mt-1 text-xs text-slate-400">{{ $m->items_count }} آیتم</div>
                        <div class="mt-3 flex gap-2"><x-admin.button :href="route('panel.menus.items', $m)" variant="soft" size="sm" icon="tree-structure">آیتم‌ها</x-admin.button>
                            <x-admin.button :href="route('panel.menus.edit', $m)" variant="secondary" size="sm" icon="pencil-simple">ویرایش</x-admin.button>
                            <x-admin.confirm-delete :action="route('panel.menus.destroy', $m)" icon-only /></div>
                    </div>
                @endforeach
            </div>
        @endif
        <x-slot:footer>{{ $menus->links('vendor.pagination.admin') }}</x-slot:footer>
    </x-admin.card>
</x-admin.layout>
