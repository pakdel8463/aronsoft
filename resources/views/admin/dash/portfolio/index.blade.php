@php
    $statusColors = ['published' => 'mint', 'draft' => 'slate', 'private' => 'amber'];
    $statusLabels = ['published' => 'منتشر شده', 'draft' => 'پیش‌نویس', 'private' => 'خصوصی'];
    $thumb = fn ($p) => $p->featured_image ? (preg_match('#^(https?:)?//#', $p->featured_image) ? $p->featured_image : asset(ltrim($p->featured_image, '/'))) : null;
@endphp
<x-admin.layout title="نمونه‌کارها" subtitle="مدیریت پروژه‌های پورتفولیو" :breadcrumbs="[['label' => 'نمونه‌کارها']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.portfolios.create')" icon="plus">نمونه‌کار جدید</x-admin.button>
    </x-slot:actions>

    <x-admin.card :padding="false">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-line p-4 sm:p-5">
            <div class="min-w-[12rem] flex-1">
                <div class="relative"><i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i><input type="text" name="q" value="{{ $q }}" placeholder="جستجو در عنوان، کارفرما یا نامک…" aria-label="جستجو" class="a-input ps-10"></div>
            </div>
            <div class="w-full sm:w-48">
                <select name="status" aria-label="وضعیت" class="a-select"><option value="">همه‌ی وضعیت‌ها</option>@foreach ($statusLabels as $k => $v)<option value="{{ $k }}" @selected((string) $k === (string) $status)>{{ $v }}</option>@endforeach</select>
            </div>
            <x-admin.button type="submit" variant="soft" icon="funnel">اعمال</x-admin.button>
            @if ($q !== '' || $status)
                <x-admin.button :href="route('panel.portfolios.index')" variant="ghost">پاک کردن</x-admin.button>
            @endif
        </form>

        <x-admin.table flush>
            <x-slot:head>
                <x-admin.th>پروژه</x-admin.th>
                <x-admin.th>کارفرما</x-admin.th>
                <x-admin.th>دسته‌بندی</x-admin.th>
                <x-admin.th>وضعیت</x-admin.th>
                <x-admin.th>تصاویر</x-admin.th>
                <x-admin.th>تاریخ</x-admin.th>
                <x-admin.th></x-admin.th>
            </x-slot:head>
            @forelse ($portfolios as $p)
                <tr>
                    <x-admin.td>
                        <div class="flex min-w-[14rem] items-center gap-3">
                            @if ($thumb($p))
                                <img src="{{ $thumb($p) }}" alt="" class="size-12 shrink-0 rounded-xl border border-line object-cover" loading="lazy">
                            @else
                                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-slate-100 text-xl text-slate-400"><i class="ph ph-image"></i></span>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('panel.portfolios.edit', $p) }}" class="block truncate font-extrabold text-ink hover:text-aron-700">{{ $p->title }}</a>
                                <span class="block truncate text-xs text-slate-400" dir="ltr">{{ $p->slug }}</span>
                            </div>
                        </div>
                    </x-admin.td>
                    <x-admin.td muted>{{ $p->client_name ?: '—' }}</x-admin.td>
                    <x-admin.td>
                        <div class="flex flex-wrap gap-1">
                            @forelse ($p->categories as $c)<x-admin.badge color="brand">{{ $c->name }}</x-admin.badge>@empty<span class="text-slate-300">—</span>@endforelse
                        </div>
                    </x-admin.td>
                    <x-admin.td><x-admin.badge :color="$statusColors[$p->status] ?? 'slate'" dot>{{ $statusLabels[$p->status] ?? $p->status }}</x-admin.badge></x-admin.td>
                    <x-admin.td muted>{{ \App\Helpers\Fa::toFa($p->images_count) }}</x-admin.td>
                    <x-admin.td muted nowrap>{{ \App\Helpers\Fa::toFa(jdate($p->created_at)->format('Y/m/d')) }}</x-admin.td>
                    <x-admin.td class="text-end" nowrap>
                        <div class="flex items-center justify-end gap-1">
                            @if ($p->status === 'published')
                                <x-admin.button :href="route('portfolio.show', $p->slug)" variant="ghost" size="sm" icon="arrow-square-out" icon-only target="_blank" rel="noopener" title="مشاهده در سایت" aria-label="مشاهده در سایت" />
                            @endif
                            <x-admin.button :href="route('panel.portfolios.edit', $p)" variant="soft" size="sm" icon="pencil-simple" icon-only title="ویرایش" aria-label="ویرایش" />
                            <x-admin.confirm-delete :action="route('panel.portfolios.destroy', $p)" message="این نمونه‌کار و تصاویر گالری آن برای همیشه حذف می‌شود. ادامه می‌دهید؟" icon-only size="sm" />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr><td colspan="7">
                    <x-admin.empty-state icon="briefcase" :title="($q !== '' || $status) ? 'نتیجه‌ای پیدا نشد' : 'هنوز نمونه‌کاری ثبت نشده'" :description="($q !== '' || $status) ? 'فیلترها را تغییر دهید.' : 'اولین پروژه‌ی خود را به پورتفولیو اضافه کنید.'">
                        <x-admin.button :href="route('panel.portfolios.create')" icon="plus">نمونه‌کار جدید</x-admin.button>
                    </x-admin.empty-state>
                </td></tr>
            @endforelse
        </x-admin.table>
        @if ($portfolios->hasPages())
            <x-slot:footer><x-admin.pagination :paginator="$portfolios" class="!mt-0" /></x-slot:footer>
        @endif
    </x-admin.card>
</x-admin.layout>
