@php
    $typeColors = ['wordpress_theme' => 'sky', 'wordpress_plugin' => 'violet', 'laravel_package' => 'coral', 'linux_script' => 'amber'];
    $thumb = fn ($p) => $p->featured_image ? (preg_match('#^(https?:)?//#', $p->featured_image) ? $p->featured_image : asset(ltrim($p->featured_image, '/'))) : null;
    $fa = fn ($v) => \App\Helpers\Fa::toFa(number_format((int) $v));
@endphp
<x-admin.layout title="محصولات" subtitle="قالب‌ها، افزونه‌ها، پکیج‌ها و اسکریپت‌ها" :breadcrumbs="[['label' => 'محصولات']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.products.create')" icon="plus">محصول جدید</x-admin.button>
    </x-slot:actions>

    <x-admin.card :padding="false">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-line p-4 sm:p-5">
            <div class="min-w-[12rem] flex-1">
                <div class="relative"><i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i><input type="text" name="q" value="{{ $q }}" placeholder="جستجوی محصول…" aria-label="جستجو" class="a-input ps-10"></div>
            </div>
            <div class="w-full sm:w-44">
                <select name="type" aria-label="نوع" class="a-select"><option value="">همه‌ی انواع</option>@foreach (\App\Models\Product::TYPES as $k => $v)<option value="{{ $k }}" @selected((string) $k === (string) $type)>{{ $v }}</option>@endforeach</select>
            </div>
            <div class="w-full sm:w-40">
                <select name="state" aria-label="وضعیت" class="a-select"><option value="">همه‌ی وضعیت‌ها</option>@foreach (['active' => 'فعال', 'inactive' => 'غیرفعال'] as $k => $v)<option value="{{ $k }}" @selected((string) $k === (string) $state)>{{ $v }}</option>@endforeach</select>
            </div>
            <x-admin.button type="submit" variant="soft" icon="funnel">اعمال</x-admin.button>
            @if ($q !== '' || $type || $state)
                <x-admin.button :href="route('panel.products.index')" variant="ghost">پاک کردن</x-admin.button>
            @endif
        </form>

        <x-admin.table flush>
            <x-slot:head>
                <x-admin.th>محصول</x-admin.th>
                <x-admin.th>نوع</x-admin.th>
                <x-admin.th>قیمت (تومان)</x-admin.th>
                <x-admin.th>نسخه</x-admin.th>
                <x-admin.th>فروش</x-admin.th>
                <x-admin.th>فعال</x-admin.th>
                <x-admin.th></x-admin.th>
            </x-slot:head>
            @forelse ($products as $p)
                <tr>
                    <x-admin.td>
                        <div class="flex min-w-[14rem] items-center gap-3">
                            @if ($thumb($p))
                                <img src="{{ $thumb($p) }}" alt="" class="size-12 shrink-0 rounded-xl border border-line object-cover" loading="lazy">
                            @else
                                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-slate-100 text-xl text-slate-400"><i class="ph ph-package"></i></span>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('panel.products.edit', $p) }}" class="block truncate font-extrabold text-ink hover:text-aron-700">{{ $p->title }}</a>
                                <span class="block truncate text-xs text-slate-400" dir="ltr">{{ $p->slug }}</span>
                            </div>
                        </div>
                    </x-admin.td>
                    <x-admin.td><x-admin.badge :color="$typeColors[$p->type] ?? 'slate'">{{ \App\Models\Product::TYPES[$p->type] ?? $p->type }}</x-admin.badge></x-admin.td>
                    <x-admin.td nowrap>
                        @if ($p->isFree())
                            <x-admin.badge color="mint">رایگان</x-admin.badge>
                        @elseif ($p->hasDiscount())
                            <span class="font-extrabold text-ink">{{ $fa($p->discount_price) }}</span>
                            <span class="ms-1 text-xs text-slate-400 line-through">{{ $fa($p->price) }}</span>
                        @else
                            <span class="font-extrabold text-ink">{{ $fa($p->price) }}</span>
                        @endif
                    </x-admin.td>
                    <x-admin.td muted nowrap><span dir="ltr">{{ $p->version }}</span></x-admin.td>
                    <x-admin.td>
                        @if ($p->is_for_sale)<x-admin.badge color="brand" icon="shopping-cart">قابل خرید</x-admin.badge>@else<x-admin.badge>معرفی</x-admin.badge>@endif
                    </x-admin.td>
                    <x-admin.td>
                        <form method="POST" action="{{ route('panel.products.toggle', $p) }}" class="inline-flex">
                            @csrf
                            <button type="submit" class="rounded-full focus:outline-none focus-visible:ring-4 focus-visible:ring-aron-100" title="تغییر وضعیت" aria-label="تغییر وضعیت فعال بودن">
                                <x-admin.badge :color="$p->is_active ? 'mint' : 'slate'" dot>{{ $p->is_active ? 'فعال' : 'غیرفعال' }}</x-admin.badge>
                            </button>
                        </form>
                    </x-admin.td>
                    <x-admin.td class="text-end" nowrap>
                        <div class="flex items-center justify-end gap-1">
                            @if ($p->is_active)
                                <x-admin.button :href="route('products.show', $p->slug)" variant="ghost" size="sm" icon="arrow-square-out" icon-only target="_blank" rel="noopener" title="مشاهده در سایت" aria-label="مشاهده در سایت" />
                            @endif
                            <x-admin.button :href="route('panel.products.edit', $p)" variant="soft" size="sm" icon="pencil-simple" icon-only title="ویرایش" aria-label="ویرایش" />
                            <x-admin.confirm-delete :action="route('panel.products.destroy', $p)" message="این محصول و فایل‌ها و تصاویر آن حذف می‌شود. ادامه می‌دهید؟" icon-only size="sm" />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr><td colspan="7">
                    <x-admin.empty-state icon="package" :title="($q !== '' || $type || $state) ? 'نتیجه‌ای پیدا نشد' : 'هنوز محصولی ثبت نشده'" :description="($q !== '' || $type || $state) ? 'فیلترها را تغییر دهید.' : 'اولین محصول خود را اضافه کنید.'">
                        <x-admin.button :href="route('panel.products.create')" icon="plus">محصول جدید</x-admin.button>
                    </x-admin.empty-state>
                </td></tr>
            @endforelse
        </x-admin.table>
        @if ($products->hasPages())
            <x-slot:footer><x-admin.pagination :paginator="$products" class="!mt-0" /></x-slot:footer>
        @endif
    </x-admin.card>
</x-admin.layout>
