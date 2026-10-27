@php($isEdit = $permission->exists)
<form method="POST" action="{{ $isEdit ? route('panel.permissions.update', $permission) : route('panel.permissions.store') }}" class="max-w-2xl">
    @csrf @if ($isEdit) @method('PUT') @endif
    <x-admin.card title="مشخصات دسترسی" icon="key">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.input name="name" label="نام (فارسی)" required :value="$permission->name" placeholder="مثلاً: مشاهده لیدها" />
            <x-admin.input name="slug" label="شناسه (انگلیسی)" required :value="$permission->slug" ltr placeholder="leads-index" hint="در کد با @@can('شناسه') استفاده می‌شود." />
            <div class="sm:col-span-2">
                <x-admin.input name="group" label="گروه" :value="$permission->group" list="perm-groups" placeholder="مثلاً: فروش و ارتباط" hint="دسترسی‌ها در ماتریس بر اساس گروه دسته‌بندی می‌شوند." />
                <datalist id="perm-groups">@foreach ($groups as $g)<option value="{{ $g }}">@endforeach</datalist>
            </div>
        </div>
        @if ($isEdit)<p class="mt-4 text-xs text-slate-400">در {{ $permission->roles_count }} نقش و {{ $permission->users_count }} کاربر استفاده شده است.</p>@endif
        <x-slot:footer>
            <div class="flex gap-2"><x-admin.button type="submit" icon="floppy-disk" loading>{{ $isEdit ? 'ذخیره' : 'ساخت دسترسی' }}</x-admin.button>
                <x-admin.button :href="route('panel.permissions.index')" variant="secondary">انصراف</x-admin.button></div>
        </x-slot:footer>
    </x-admin.card>
</form>
