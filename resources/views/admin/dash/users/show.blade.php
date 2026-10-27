@php
    $p = $user->profile;
    $perms = $user->roles->flatMap->permissions->unique('id');
    $row = fn ($label, $value) => '<div class="flex items-start justify-between gap-4 border-b border-line/70 py-3 last:border-0"><dt class="text-sm text-slate-500">'.e($label).'</dt><dd class="text-sm font-bold text-ink">'.$value.'</dd></div>';
@endphp
<x-admin.layout :title="$user->name" subtitle="پروفایل و دسترسی‌های کاربر" :breadcrumbs="[['label' => 'کاربران', 'url' => route('panel.users.index')], ['label' => $user->name]]">
    <x-slot:actions>
        @can('users.edit')<x-admin.button :href="route('panel.users.edit', $user)" icon="pencil-simple">ویرایش</x-admin.button>@endcan
        @if ($user->email)<x-admin.button :href="'mailto:'.$user->email" variant="secondary" icon="envelope-simple">ایمیل</x-admin.button>@endif
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <x-admin.card>
                <div class="flex flex-col items-center gap-3 text-center">
                    @include('admin.dash.users._avatar', ['user' => $user, 'size' => 'size-28 text-4xl'])
                    <div><h2 class="text-lg font-extrabold text-ink">{{ $user->name }}</h2><p class="text-sm text-slate-400" dir="ltr">{{ $user->email }}</p></div>
                    <div class="flex flex-wrap justify-center gap-1.5">
                        <x-admin.badge :color="['superuser' => 'violet', 'staff' => 'brand', 'user' => 'slate'][$user->access_level]">{{ $levels[$user->access_level] }}</x-admin.badge>
                        @if ($p?->status ?? $user->email_verified_at)<x-admin.badge color="mint" dot>فعال</x-admin.badge>@else<x-admin.badge color="slate" dot>غیرفعال</x-admin.badge>@endif
                    </div>
                </div>
            </x-admin.card>
            <div class="grid grid-cols-2 gap-4">
                <x-admin.stat-card label="سفارش" :value="$orderCount" icon="shopping-bag" color="sky" />
                <x-admin.stat-card label="پیش‌فاکتور" :value="$proformaCount" icon="receipt" color="amber" />
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <x-admin.card title="اطلاعات" icon="identification-card">
                <dl>
                    {!! $row('موبایل', '<span dir="ltr">'.e($p?->mobile ?: '—').'</span>') !!}
                    {!! $row('استان / شهر', e(trim(($p?->province ?? '').' / '.($p?->city ?? ''), ' /')) ?: '—') !!}
                    {!! $row('نشانی', e($p?->address ?: '—')) !!}
                    {!! $row('تأیید ایمیل', $user->email_verified_at ? e(jdate($user->email_verified_at)->format('Y/m/d H:i')) : '—') !!}
                    {!! $row('تاریخ عضویت', $user->created_at ? e(jdate($user->created_at)->format('Y/m/d H:i')) : '—') !!}
                    @if ($p?->note){!! $row('یادداشت', '<span class="whitespace-pre-line font-medium">'.e($p->note).'</span>') !!}@endif
                </dl>
            </x-admin.card>
            <x-admin.card title="نقش‌ها و دسترسی‌ها" icon="shield-check">
                <div class="space-y-4">
                    <div><div class="mb-2 text-xs font-bold text-slate-500">نقش‌ها</div>
                        <div class="flex flex-wrap gap-1.5">@forelse ($user->roles as $r)<x-admin.badge color="brand">{{ $r->name }}</x-admin.badge>@empty<span class="text-sm text-slate-400">بدون نقش</span>@endforelse</div></div>
                    <div><div class="mb-2 text-xs font-bold text-slate-500">دسترسی مستقیم</div>
                        <div class="flex flex-wrap gap-1.5">@forelse ($user->extraPermissions as $x)<x-admin.badge color="mint">{{ $x->name }}</x-admin.badge>@empty<span class="text-sm text-slate-400">—</span>@endforelse</div></div>
                    <div><div class="mb-2 text-xs font-bold text-slate-500">ممنوع‌شده</div>
                        <div class="flex flex-wrap gap-1.5">@forelse ($user->deniedPermissions as $x)<x-admin.badge color="coral">{{ $x->name }}</x-admin.badge>@empty<span class="text-sm text-slate-400">—</span>@endforelse</div></div>
                    <p class="text-xs text-slate-400">دسترسی‌های ناشی از نقش‌ها: {{ $perms->count() }} مورد</p>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
