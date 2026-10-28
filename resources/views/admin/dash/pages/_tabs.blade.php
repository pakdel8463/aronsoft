{{-- سربرگ مشترک صفحه‌های ویرایش یک صفحه: صفحه‌ساز / تنظیمات --}}
<nav class="mb-5 flex flex-wrap items-center gap-2" aria-label="بخش‌های صفحه">
    @foreach ([['panel.pages.builder', 'صفحه‌ساز', 'layout'], ['panel.pages.edit', $page->is_template ? 'تنظیمات قالب' : 'تنظیمات و سئو', 'gear-six']] as [$r, $label, $icon])
        @php $on = request()->routeIs($r) || ($r === 'panel.pages.builder' && request()->routeIs('panel.pages.blocks.*')); @endphp
        <a href="{{ route($r, $page) }}" @if ($on) aria-current="page" @endif
           class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-sm font-bold transition {{ $on ? 'border-aron-600 bg-aron-600 text-white shadow-adm-brand' : 'border-line bg-white text-slate-600 hover:border-aron-300 hover:text-aron-700' }}">
            <i class="ph ph-{{ $icon }}"></i>{{ $label }}</a>
    @endforeach
    <span class="ms-auto flex flex-wrap items-center gap-2">
        @if ($page->is_template)
            <x-admin.badge color="violet" icon="stack">قالب</x-admin.badge>
        @else
            @if ($page->is_home)<x-admin.badge color="brand" icon="house">صفحه‌ی اصلی سایت</x-admin.badge>@endif
            <form method="POST" action="{{ route('panel.pages.publish', $page) }}" class="inline-flex">@csrf
                <button type="submit" class="rounded-full" title="{{ $page->is_active ? 'بازگشت به پیش‌نویس' : 'انتشار' }}">
                    <x-admin.badge :color="$page->is_active ? 'mint' : 'amber'" dot>{{ $page->is_active ? 'منتشرشده' : 'پیش‌نویس — برای انتشار کلیک کنید' }}</x-admin.badge>
                </button>
            </form>
        @endif
    </span>
</nav>
