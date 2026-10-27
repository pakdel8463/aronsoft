@php
    $isEdit = $menu->exists;
    $posVal = old('position', $menu->position);
    $custom = $posVal === '_custom' || ($posVal && ! isset($positions[$posVal]));
    $opts = $positions + ['_custom' => 'موقعیت سفارشی…'];
@endphp
<form method="POST" action="{{ $isEdit ? route('panel.menus.update', $menu) : route('panel.menus.store') }}" class="max-w-2xl">
    @csrf @if ($isEdit) @method('PUT') @endif
    <x-admin.card title="مشخصات منو" icon="list-bullets">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.input name="name" label="نام منو" required :value="$menu->name" placeholder="منوی اصلی" />
            <x-admin.input name="slug" label="نامک (انگلیسی)" required :value="$menu->slug" ltr placeholder="main-menu" />
            <div class="sm:col-span-2" data-menu-position>
                <x-admin.select name="position" label="موقعیت نمایش" required :options="$opts" :selected="$custom ? '_custom' : $posVal" hint="هر موقعیت فقط یک منو دارد؛ قالب سایت منوی هر موقعیت را در جای مشخص خودش نشان می‌دهد." />
                <div class="mt-3 {{ $custom ? '' : 'hidden' }}" data-position-custom>
                    <x-admin.input name="position_custom" label="کد موقعیت سفارشی" ltr :value="$custom && $posVal !== '_custom' ? $posVal : old('position_custom')" placeholder="sidebar" />
                </div>
            </div>
        </div>
        <x-slot:footer>
            <div class="flex flex-wrap gap-2"><x-admin.button type="submit" icon="floppy-disk" loading>{{ $isEdit ? 'ذخیره' : 'ساخت و چیدن آیتم‌ها' }}</x-admin.button>
                @if ($isEdit)<x-admin.button :href="route('panel.menus.items', $menu)" variant="soft" icon="tree-structure">مدیریت آیتم‌ها ({{ $menu->items_count }})</x-admin.button>@endif
                <x-admin.button :href="route('panel.menus.index')" variant="secondary">انصراف</x-admin.button></div>
        </x-slot:footer>
    </x-admin.card>
</form>
<script>
(function () {
    const w = document.querySelector('[data-menu-position]'); if (!w) return;
    const sel = w.querySelector('select'), box = w.querySelector('[data-position-custom]');
    sel.addEventListener('change', () => box.classList.toggle('hidden', sel.value !== '_custom'));
})();
</script>
