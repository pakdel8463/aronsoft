@php
    use App\Models\ProductPage;
    $fa = fn ($v) => \App\Helpers\Fa::toFa((string) $v);
    $total = (int) collect($counts)->sum();
    $filtered = $q !== '' || $status;
    $chips = [
        ['', 'همه', $total],
        ['published', 'منتشرشده', (int) ($counts['published'] ?? 0)],
        ['draft', 'پیش‌نویس', (int) ($counts['draft'] ?? 0)],
    ];
@endphp
<x-admin.layout title="لندینگ محصولات" subtitle="صفحه‌های معرفی محصولات آرون‌سافت در آدرس /products"
                :breadcrumbs="[['label' => 'لندینگ محصولات']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.product-pages.create')" icon="plus">ساخت صفحه‌ی محصول</x-admin.button>
    </x-slot:actions>

    <x-admin.card :padding="false">
        <div class="space-y-3 border-b border-line p-4 sm:p-5">
            <nav class="flex flex-wrap gap-2" aria-label="فیلتر وضعیت">
                @foreach ($chips as [$key, $label, $count])
                    @php $on = (string) $status === (string) $key || ($key === '' && ! $status); @endphp
                    <a href="{{ route('panel.product-pages.index', array_filter(['q' => $q, 'status' => $key])) }}"
                       @if ($on) aria-current="true" @endif
                       class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-sm font-bold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-aron-100 {{ $on ? 'border-aron-600 bg-aron-600 text-white shadow-adm-brand' : 'border-line bg-white text-slate-600 hover:border-aron-300 hover:text-aron-700' }}">
                        {{ $label }}
                        <span class="rounded-full px-2 py-0.5 text-[11px] leading-none {{ $on ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $fa($count) }}</span>
                    </a>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('panel.product-pages.index') }}" class="flex flex-wrap items-center gap-3">
                @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <div class="relative min-w-[12rem] flex-1">
                    <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                    <input type="search" name="q" value="{{ $q }}" placeholder="جستجوی نام، نام انگلیسی یا نشانی…" aria-label="جستجو" class="a-input ps-10">
                </div>
                <x-admin.button type="submit" variant="soft" icon="funnel">جستجو</x-admin.button>
                @if ($filtered)
                    <x-admin.button :href="route('panel.product-pages.index')" variant="ghost">پاک کردن فیلتر</x-admin.button>
                @endif
            </form>
        </div>

        <x-admin.table flush>
            <x-slot:head>
                <x-admin.th>محصول</x-admin.th>
                <x-admin.th>نشانی</x-admin.th>
                <x-admin.th>وضعیت</x-admin.th>
                <x-admin.th>ترتیب</x-admin.th>
                <x-admin.th>آخرین ویرایش</x-admin.th>
                <x-admin.th></x-admin.th>
            </x-slot:head>
            @forelse ($pages as $p)
                @php
                    $scheduled = $p->status === 'published' && ! $p->isLive();
                @endphp
                <tr>
                    <x-admin.td>
                        <div class="flex min-w-[14rem] items-center gap-3">
                            @if ($p->logoUrl())
                                <img src="{{ $p->logoUrl() }}" alt="" class="size-12 shrink-0 rounded-xl border border-line bg-white object-contain p-1" loading="lazy">
                            @else
                                <span class="grid size-12 shrink-0 place-items-center rounded-xl text-xl" style="background:linear-gradient(135deg,{{ $p->accentColor() }},{{ $p->accent2Color() }});color:{{ $p->onAccent() }}">
                                    <i class="ph {{ $p->icon ?: 'ph-rocket-launch' }}"></i>
                                </span>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('panel.product-pages.edit', $p) }}" class="block truncate font-extrabold text-ink hover:text-aron-700">{{ $p->name }}</a>
                                @if ($p->name_en)<span class="block truncate text-xs text-slate-400" dir="ltr">{{ $p->name_en }}</span>@endif
                                <span class="block truncate text-[11px] font-bold text-aron-600">قالب: {{ \App\Support\ProductPageSchema::template($p->template)['label'] ?? $p->template }}</span>
                            </div>
                        </div>
                    </x-admin.td>
                    <x-admin.td nowrap>
                        <a href="{{ $p->publicUrl() }}" target="_blank" rel="noopener" dir="ltr" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-aron-700">/products/{{ $p->slug }}<i class="ph ph-arrow-square-out"></i></a>
                    </x-admin.td>
                    <x-admin.td>
                        <form method="POST" action="{{ route('panel.product-pages.toggle', $p) }}" class="inline-flex">
                            @csrf
                            <button type="submit" class="rounded-full focus:outline-none focus-visible:ring-4 focus-visible:ring-aron-100" title="{{ $p->status === 'published' ? 'بازگشت به پیش‌نویس' : 'انتشار' }}" aria-label="{{ $p->status === 'published' ? 'بازگشت به پیش‌نویس' : 'انتشار' }}">
                                @if ($scheduled)
                                    <x-admin.badge color="amber" dot>زمان‌بندی‌شده</x-admin.badge>
                                @else
                                    <x-admin.badge :color="$p->status === 'published' ? 'mint' : 'slate'" dot>{{ ProductPage::STATUSES[$p->status] ?? $p->status }}</x-admin.badge>
                                @endif
                            </button>
                        </form>
                    </x-admin.td>
                    <x-admin.td muted nowrap>{{ $fa($p->sort_order) }}</x-admin.td>
                    <x-admin.td muted nowrap>{{ $p->updated_at ? jdate($p->updated_at)->format('Y/m/d H:i') : '—' }}</x-admin.td>
                    <x-admin.td class="text-end" nowrap>
                        <div class="flex items-center justify-end gap-1">
                            <x-admin.button :href="$p->publicUrl()" variant="ghost" size="sm" icon="arrow-square-out" icon-only target="_blank" rel="noopener" :title="$p->isLive() ? 'مشاهده در سایت' : 'پیش‌نمایش'" :aria-label="$p->isLive() ? 'مشاهده در سایت' : 'پیش‌نمایش'" />
                            <x-admin.button :href="route('panel.product-pages.edit', $p)" variant="soft" size="sm" icon="pencil-simple" icon-only title="ویرایش" aria-label="ویرایش" />
                            <form method="POST" action="{{ route('panel.product-pages.toggle', $p) }}" class="inline-flex">@csrf
                                <x-admin.button type="submit" variant="ghost" size="sm" :icon="$p->status === 'published' ? 'eye-slash' : 'paper-plane-tilt'" icon-only :title="$p->status === 'published' ? 'بازگشت به پیش‌نویس' : 'انتشار'" :aria-label="$p->status === 'published' ? 'بازگشت به پیش‌نویس' : 'انتشار'" />
                            </form>
                            <form method="POST" action="{{ route('panel.product-pages.duplicate', $p) }}" class="inline-flex">@csrf
                                <x-admin.button type="submit" variant="ghost" size="sm" icon="copy-simple" icon-only title="تکثیر" aria-label="تکثیر" />
                            </form>
                            <x-admin.confirm-delete :action="route('panel.product-pages.destroy', $p)" message="صفحه‌ی «{{ $p->name }}» و تمام محتوای آن حذف می‌شود. ادامه می‌دهید؟" icon-only size="sm" />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr><td colspan="6">
                    <x-admin.empty-state icon="rocket-launch" :title="$filtered ? 'نتیجه‌ای پیدا نشد' : 'هنوز صفحه‌ی محصولی ساخته نشده'" :description="$filtered ? 'عبارت جستجو یا فیلتر وضعیت را تغییر دهید.' : 'برای هر محصول یک صفحه‌ی معرفی (لندینگ) با بخش‌های دلخواه بسازید.'">
                        @if ($filtered)
                            <x-admin.button :href="route('panel.product-pages.index')" variant="secondary">پاک کردن فیلتر</x-admin.button>
                        @else
                            <div class="flex flex-wrap items-center justify-center gap-3">
                                <form method="POST" action="{{ route('panel.product-pages.seed') }}">
                                    @csrf
                                    <x-admin.button type="submit" icon="magic-wand">ساخت سه محصول پیش‌فرض (کالریتو، مانیار، هوماواچ)</x-admin.button>
                                </form>
                                <x-admin.button :href="route('panel.product-pages.create')" variant="secondary" icon="plus">ساخت با قالب دلخواه</x-admin.button>
                            </div>
                        @endif
                    </x-admin.empty-state>
                </td></tr>
            @endforelse
        </x-admin.table>
        @if ($pages->hasPages())
            <x-slot:footer><x-admin.pagination :paginator="$pages" class="!mt-0" /></x-slot:footer>
        @endif
    </x-admin.card>
</x-admin.layout>
