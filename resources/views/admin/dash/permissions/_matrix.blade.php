@php
    $name = $name ?? 'permissions';
    $selected = array_map('intval', (array) old(rtrim($name, '[]'), $selected ?? []));
    $uid = 'pm_'.\Illuminate\Support\Str::random(5);
@endphp
<div data-perm-matrix class="space-y-4">
    <div class="flex flex-wrap items-center gap-2">
        <div class="relative min-w-52 flex-1">
            <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
            <input type="search" data-pm-search placeholder="جستجوی دسترسی یا گروه…" class="a-input ps-10" autocomplete="off">
        </div>
        <button type="button" data-pm-all class="a-btn a-btn-soft a-btn-sm"><i class="ph ph-checks"></i>انتخاب همه</button>
        <button type="button" data-pm-none class="a-btn a-btn-secondary a-btn-sm"><i class="ph ph-x"></i>هیچ‌کدام</button>
        <x-admin.badge color="brand"><span data-pm-count dir="ltr">0</span> انتخاب‌شده</x-admin.badge>
    </div>

    @forelse ($groups as $group => $perms)
        <section data-pm-group="{{ $group }}" class="rounded-2xl border border-line">
            <header class="flex items-center justify-between gap-3 rounded-t-2xl bg-slate-50/80 px-4 py-3">
                <label class="flex cursor-pointer items-center gap-3 text-sm font-extrabold text-ink">
                    <input type="checkbox" data-pm-group-toggle class="a-check"> {{ $group }}
                </label>
                <span class="text-xs font-bold text-slate-400" dir="ltr" data-pm-group-count></span>
            </header>
            <div class="grid gap-x-4 gap-y-1 p-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($perms as $p)
                    <div data-pm-item data-pm-label="{{ $p->name }} {{ $p->slug }}">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl px-2 py-2 hover:bg-aron-50/50">
                            <input type="checkbox" data-pm-box name="{{ $name }}[]" value="{{ $p->id }}" class="a-check mt-0.5" @checked(in_array($p->id, $selected, true))>
                            <span class="min-w-0"><span class="block text-sm font-bold text-slate-700">{{ $p->name }}</span><span class="block truncate text-[11px] text-slate-400" dir="ltr">{{ $p->slug }}</span></span>
                        </label>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <x-admin.empty-state icon="key" title="هنوز دسترسی‌ای تعریف نشده" description="ابتدا از بخش «دسترسی‌ها» دسترسی بسازید.">
            @if (Route::has('panel.permissions.index'))<x-admin.button :href="route('panel.permissions.index')" variant="soft" icon="key">رفتن به دسترسی‌ها</x-admin.button>@endif
        </x-admin.empty-state>
    @endforelse
    <p data-pm-empty class="hidden py-6 text-center text-sm text-slate-400">موردی مطابق جستجو پیدا نشد.</p>
</div>
