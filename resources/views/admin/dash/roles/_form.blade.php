@php($isEdit = $role->exists)
<form method="POST" action="{{ $isEdit ? route('panel.roles.update', $role) : route('panel.roles.store') }}" class="grid gap-6 lg:grid-cols-3">
    @csrf @if ($isEdit) @method('PUT') @endif
    <div class="space-y-6 lg:col-span-2">
        <x-admin.card title="دسترسی‌های نقش" icon="key" subtitle="با انتخاب گروه، همه دسترسی‌های آن گروه یک‌جا انتخاب می‌شود.">
            @if (auth()->user()->isSuperAdmin() && old('is_superuser', $role->is_superuser))<p class="mb-4 rounded-2xl bg-violet-50 p-3 text-xs font-semibold text-violet-700">این نقش «دسترسی کامل» دارد و انتخاب‌های زیر برایش بی‌اثر است.</p>@endif
            @include('admin.dash.permissions._matrix', ['groups' => $permissionsByGroup, 'name' => 'permissions', 'selected' => $rolePermissions])
        </x-admin.card>
    </div>
    <div class="space-y-6">
        <x-admin.card title="مشخصات" icon="identification-badge">
            <div class="space-y-4">
                <x-admin.input name="name" label="نام نقش" required :value="$role->name" placeholder="مثلاً: ویرایشگر محتوا" />
                <x-admin.input name="slug" label="شناسه (انگلیسی)" required :value="$role->slug" ltr placeholder="content-editor" />
                @if (auth()->user()->isSuperAdmin())
                    <x-admin.toggle name="is_superuser" label="دسترسی کامل (سوپریوزر)" hint="همه دسترسی‌ها بدون محدودیت" :checked="(bool) $role->is_superuser" />
                @endif
                @if ($isEdit)<p class="text-xs text-slate-400">{{ $role->users_count }} کاربر این نقش را دارند.</p>@endif
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="flex flex-col gap-2">
                <x-admin.button type="submit" icon="floppy-disk" loading>{{ $isEdit ? 'ذخیره تغییرات' : 'ساخت نقش' }}</x-admin.button>
                <x-admin.button :href="route('panel.roles.index')" variant="secondary">انصراف</x-admin.button>
            </div>
        </x-admin.card>
    </div>
</form>
