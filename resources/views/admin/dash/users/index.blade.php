@php
    $fa = fn ($n) => number_format($n);
    $levelTone = ['superuser' => 'violet', 'staff' => 'brand', 'user' => 'slate'];
@endphp
<x-admin.layout title="کاربران" subtitle="مدیریت حساب‌ها، سطح دسترسی و نقش‌ها" :breadcrumbs="[['label' => 'کاربران']]">
    <x-slot:actions>
        @can('users.create')<x-admin.button :href="route('panel.users.create')" icon="user-plus">کاربر جدید</x-admin.button>@endcan
    </x-slot:actions>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-admin.stat-card label="کل کاربران" :value="$fa($stats['total'])" icon="users" color="brand" />
        <x-admin.stat-card label="مدیران و کارکنان" :value="$fa($stats['staff'])" icon="shield-check" color="violet" />
        <x-admin.stat-card label="ایمیل تأییدشده" :value="$fa($stats['verified'])" icon="seal-check" color="mint" />
    </div>

    <x-admin.card :padding="false">
        <form method="GET" class="flex flex-wrap items-end gap-3 p-4 sm:p-5">
            <div class="relative min-w-56 flex-1">
                <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="جستجوی نام، ایمیل یا موبایل…" class="a-input ps-10">
            </div>
            <select name="role" class="a-select w-full sm:w-44">
                <option value="">همه سطوح</option>
                @foreach ($levels as $k => $l)<option value="{{ $k }}" @selected(request('role') === $k)>{{ $l }}</option>@endforeach
            </select>
            <select name="status" class="a-select w-full sm:w-36">
                <option value="">همه وضعیت‌ها</option>
                <option value="1" @selected(request('status') === '1')>فعال</option>
                <option value="0" @selected(request('status') === '0')>غیرفعال</option>
            </select>
            <x-admin.button type="submit" variant="soft" icon="funnel">اعمال</x-admin.button>
            @if (request()->hasAny(['search', 'role', 'status']))<x-admin.button :href="route('panel.users.index')" variant="ghost" icon="x">پاک‌کردن</x-admin.button>@endif
        </form>

        @if ($users->isEmpty())
            <x-admin.empty-state icon="users" title="کاربری پیدا نشد" description="فیلترها را تغییر دهید یا کاربر تازه‌ای بسازید.">
                @can('users.create')<x-admin.button :href="route('panel.users.create')" icon="user-plus">کاربر جدید</x-admin.button>@endcan
            </x-admin.empty-state>
        @else
            <div class="hidden md:block">
                <x-admin.table flush class="px-4 pb-2">
                    <x-slot:head>
                        <x-admin.th>کاربر</x-admin.th><x-admin.th>سطح</x-admin.th><x-admin.th>موبایل</x-admin.th>
                        <x-admin.th>وضعیت</x-admin.th><x-admin.th>عضویت</x-admin.th><x-admin.th></x-admin.th>
                    </x-slot:head>
                    @foreach ($users as $u)
                        <tr>
                            <x-admin.td>
                                <a href="{{ route('panel.users.show', $u) }}" class="flex items-center gap-3">
                                    @include('admin.dash.users._avatar', ['user' => $u])
                                    <span class="min-w-0"><span class="block truncate font-bold text-ink">{{ $u->name }}</span><span class="block truncate text-xs text-slate-400" dir="ltr">{{ $u->email }}</span></span>
                                </a>
                            </x-admin.td>
                            <x-admin.td><x-admin.badge :color="$levelTone[$u->access_level]">{{ $levels[$u->access_level] ?? '' }}</x-admin.badge>@if ($u->roles_count)<span class="ms-1 text-xs text-slate-400">+{{ $u->roles_count }} نقش</span>@endif</x-admin.td>
                            <x-admin.td muted><span dir="ltr">{{ $u->profile?->mobile ?: '—' }}</span></x-admin.td>
                            <x-admin.td>
                                @if ($u->profile?->status ?? $u->email_verified_at)<x-admin.badge color="mint" dot>فعال</x-admin.badge>@else<x-admin.badge color="slate" dot>غیرفعال</x-admin.badge>@endif
                            </x-admin.td>
                            <x-admin.td muted nowrap>{{ $u->created_at ? jdate($u->created_at)->format('Y/m/d') : '—' }}</x-admin.td>
                            <x-admin.td class="text-end" nowrap>
                                <x-admin.button :href="route('panel.users.show', $u)" variant="ghost" size="sm" icon="eye" icon-only title="مشاهده" aria-label="مشاهده" />
                                @can('users.edit')<x-admin.button :href="route('panel.users.edit', $u)" variant="ghost" size="sm" icon="pencil-simple" icon-only title="ویرایش" aria-label="ویرایش" />@endcan
                                @can('users.delete')@if ($u->id !== auth()->id())<x-admin.confirm-delete :action="route('panel.users.destroy', $u)" icon-only message="کاربر «{{ $u->name }}» حذف شود؟" />@endif @endcan
                            </x-admin.td>
                        </tr>
                    @endforeach
                </x-admin.table>
            </div>
            <div class="divide-y divide-line/70 md:hidden">
                @foreach ($users as $u)
                    <a href="{{ route('panel.users.show', $u) }}" class="flex items-center gap-3 px-4 py-3.5">
                        @include('admin.dash.users._avatar', ['user' => $u, 'size' => 'size-11 text-base'])
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-bold text-ink">{{ $u->name }}</span>
                            <span class="block truncate text-xs text-slate-400" dir="ltr">{{ $u->email }}</span>
                            <span class="mt-1.5 flex flex-wrap gap-1.5"><x-admin.badge :color="$levelTone[$u->access_level]">{{ $levels[$u->access_level] ?? '' }}</x-admin.badge>
                                @if ($u->profile?->status ?? $u->email_verified_at)<x-admin.badge color="mint" dot>فعال</x-admin.badge>@else<x-admin.badge color="slate" dot>غیرفعال</x-admin.badge>@endif</span>
                        </span>
                        <i class="ph ph-caret-left text-slate-300"></i>
                    </a>
                @endforeach
            </div>
        @endif
        <x-slot:footer>{{ $users->links('vendor.pagination.admin') }}</x-slot:footer>
    </x-admin.card>
</x-admin.layout>
