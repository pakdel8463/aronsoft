<x-admin.layout title="نقش‌ها" subtitle="گروه‌بندی دسترسی‌ها برای کارکنان" :breadcrumbs="[['label' => 'نقش‌ها']]">
    <x-slot:actions>@can('role-create')<x-admin.button :href="route('panel.roles.create')" icon="plus">نقش جدید</x-admin.button>@endcan</x-slot:actions>
    <x-admin.card :padding="false">
        @if ($roles->isEmpty())
            <x-admin.empty-state icon="shield-check" title="نقشی تعریف نشده" description="با ساختن نقش، دسترسی‌ها را یک‌جا به چند کاربر بدهید.">
                @can('role-create')<x-admin.button :href="route('panel.roles.create')" icon="plus">نقش جدید</x-admin.button>@endcan
            </x-admin.empty-state>
        @else
            <div class="hidden md:block">
                <x-admin.table class="pt-3">
                    <x-slot:head><x-admin.th>نقش</x-admin.th><x-admin.th>شناسه</x-admin.th><x-admin.th>دسترسی‌ها</x-admin.th><x-admin.th>کاربران</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
                    @foreach ($roles as $r)
                        <tr>
                            <x-admin.td><span class="font-bold text-ink">{{ $r->name }}</span> @if ($r->is_superuser)<x-admin.badge color="violet" icon="crown">دسترسی کامل</x-admin.badge>@endif</x-admin.td>
                            <x-admin.td muted><span dir="ltr">{{ $r->slug }}</span></x-admin.td>
                            <x-admin.td>{{ $r->is_superuser ? 'همه' : $r->permissions_count }}</x-admin.td>
                            <x-admin.td>{{ $r->users_count }}</x-admin.td>
                            <x-admin.td class="text-end" nowrap>
                                @can('role-edit')<x-admin.button :href="route('panel.roles.edit', $r)" variant="soft" size="sm" icon="pencil-simple">ویرایش</x-admin.button>@endcan
                                @can('role-delete')<x-admin.confirm-delete :action="route('panel.roles.destroy', $r)" icon-only :message="'نقش «'.$r->name.'» حذف شود؟ '.$r->users_count.' کاربر آن را از دست می‌دهند.'" />@endcan
                            </x-admin.td>
                        </tr>
                    @endforeach
                </x-admin.table>
            </div>
            <div class="divide-y divide-line/70 md:hidden">
                @foreach ($roles as $r)
                    <div class="flex items-center gap-3 px-4 py-3.5">
                        <span class="grid size-11 place-items-center rounded-2xl bg-aron-50 text-xl text-aron-600"><i class="ph ph-shield-check"></i></span>
                        <a href="{{ route('panel.roles.edit', $r) }}" class="min-w-0 flex-1"><span class="block truncate font-bold text-ink">{{ $r->name }}</span>
                            <span class="text-xs text-slate-400">{{ $r->is_superuser ? 'دسترسی کامل' : $r->permissions_count.' دسترسی' }} · {{ $r->users_count }} کاربر</span></a>
                        @can('role-delete')<x-admin.confirm-delete :action="route('panel.roles.destroy', $r)" icon-only />@endcan
                    </div>
                @endforeach
            </div>
        @endif
        <x-slot:footer>{{ $roles->links('vendor.pagination.admin') }}</x-slot:footer>
    </x-admin.card>
</x-admin.layout>
