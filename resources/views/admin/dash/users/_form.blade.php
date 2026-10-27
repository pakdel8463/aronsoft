{{-- $user (new User for create), $levels, $roles, $permissionsByGroup (edit only) --}}
@php
    $isEdit = $user->exists;
    $p = $user->profile;
    $userRoleIds = $isEdit ? $user->roles->pluck('id')->all() : [];
@endphp
<form method="POST" action="{{ $isEdit ? route('panel.users.update', $user) : route('panel.users.store') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3" data-avatar-form>
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="space-y-6 lg:col-span-2">
        <x-admin.tabs :tabs="$isEdit ? ['profile' => ['label' => 'مشخصات', 'icon' => 'identification-card'], 'access' => ['label' => 'نقش و دسترسی', 'icon' => 'shield-check']] : ['profile' => ['label' => 'مشخصات', 'icon' => 'identification-card'], 'access' => ['label' => 'دسترسی', 'icon' => 'shield-check']]">
            <x-admin.tab name="profile" class="space-y-6">
                <x-admin.card title="اطلاعات حساب" icon="user">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="first_name" label="نام" required :value="$p->first_name ?? ''" />
                        <x-admin.input name="last_name" label="نام خانوادگی" required :value="$p->last_name ?? ''" />
                        <x-admin.input name="email" type="email" label="ایمیل" required :value="$user->email" />
                        <x-admin.input name="password" type="password" :label="$isEdit ? 'رمز عبور جدید' : 'رمز عبور'" :required="! $isEdit" :hint="$isEdit ? 'برای تغییر ندادن، خالی بگذارید. حداقل ۸ نویسه.' : 'حداقل ۸ نویسه.'" autocomplete="new-password" />
                        <x-admin.input name="mobile" type="tel" label="موبایل" :value="$p->mobile ?? ''" />
                    </div>
                </x-admin.card>
                <x-admin.card title="نشانی" icon="map-pin">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="province" label="استان" :value="$p->province ?? ''" />
                        <x-admin.input name="city" label="شهر" :value="$p->city ?? ''" />
                        <x-admin.input name="address" label="نشانی" class="sm:col-span-2" :value="$p->address ?? ''" />
                        <x-admin.textarea name="note" label="یادداشت داخلی" hint="فقط برای مدیران قابل مشاهده است." rows="3" class="sm:col-span-2" :value="$p->note ?? ''" />
                    </div>
                </x-admin.card>
            </x-admin.tab>

            <x-admin.tab name="access" class="space-y-6">
                <x-admin.card title="سطح دسترسی" icon="shield-check" subtitle="سطح پایه تعیین می‌کند کاربر به پنل مدیریت راه دارد یا نه.">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.select name="level" label="سطح" :options="$levels" :selected="$isEdit ? $user->access_level : 'user'" required />
                        <div class="flex items-end pb-1">
                            <x-admin.toggle name="status" label="حساب فعال" hint="فعال‌سازی، ایمیل را تأییدشده می‌کند." :checked="$isEdit ? (bool) ($p->status ?? $user->email_verified_at) : true" />
                        </div>
                    </div>
                </x-admin.card>
                <x-admin.card title="نقش‌ها" icon="identification-badge">
                    @if ($roles->isEmpty())
                        <p class="text-sm text-slate-400">نقشی تعریف نشده است.</p>
                    @else
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($roles as $r)
                                <x-admin.checkbox name="roles[]" :value="$r->id" :label="$r->name" :hint="$r->is_superuser ? 'دسترسی کامل' : $r->slug" :checked="in_array($r->id, $userRoleIds)" />
                            @endforeach
                        </div>
                    @endif
                </x-admin.card>
                @if ($isEdit)
                    <x-admin.card title="دسترسی‌های مستقیم" icon="plus-circle" subtitle="علاوه بر نقش‌ها به این کاربر داده می‌شود.">
                        @include('admin.dash.permissions._matrix', ['groups' => $permissionsByGroup, 'name' => 'extra_permissions', 'selected' => $user->extraPermissions->pluck('id')->all()])
                    </x-admin.card>
                    <x-admin.card title="دسترسی‌های ممنوع" icon="prohibit" subtitle="حتی اگر نقش یا دسترسی مستقیم اجازه بدهد، این موارد بسته می‌شوند.">
                        @include('admin.dash.permissions._matrix', ['groups' => $permissionsByGroup, 'name' => 'denied_permissions', 'selected' => $user->deniedPermissions->pluck('id')->all()])
                    </x-admin.card>
                @endif
            </x-admin.tab>
        </x-admin.tabs>
    </div>

    <div class="space-y-6">
        <x-admin.card title="تصویر پروفایل" icon="image">
            <div class="flex flex-col items-center gap-4 text-center">
                <div data-avatar-preview>@include('admin.dash.users._avatar', ['user' => $user->exists ? $user : new \App\Models\User(['name' => 'ک']), 'size' => 'size-28 text-4xl'])</div>
                <input type="file" name="avatar_file" accept="image/png,image/jpeg,image/webp" class="a-input" data-avatar-input @if ($isEdit) data-upload-url="{{ route('panel.users.avatar.upload', $user) }}" @endif>
                <p class="text-xs text-slate-400">JPG، PNG یا WebP تا ۲ مگابایت.</p>
                @if ($isEdit && $p?->avatar)<x-admin.checkbox name="remove_avatar" label="حذف تصویر فعلی" />@endif
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="flex flex-col gap-2">
                <x-admin.button type="submit" icon="floppy-disk" loading>{{ $isEdit ? 'ذخیره تغییرات' : 'ساخت کاربر' }}</x-admin.button>
                <x-admin.button :href="$isEdit ? route('panel.users.show', $user) : route('panel.users.index')" variant="secondary">انصراف</x-admin.button>
            </div>
        </x-admin.card>
    </div>
</form>
