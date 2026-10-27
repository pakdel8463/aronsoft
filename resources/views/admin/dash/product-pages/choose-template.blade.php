@php
    use App\Support\ProductPageSchema as Schema;
@endphp
<x-admin.layout title="انتخاب قالب صفحه‌ی محصول" subtitle="یک قالب انتخاب کنید؛ صفحه با محتوای نمونه ساخته می‌شود و همه‌ی متن‌ها، تصاویر و رنگ‌ها قابل ویرایش است."
                :breadcrumbs="[['label' => 'لندینگ محصولات', 'url' => route('panel.product-pages.index')], ['label' => 'قالب جدید']]">
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($templates as $key => $t)
            @php $pal = Schema::paletteValues($key, []); @endphp
            <a href="{{ route('panel.product-pages.create', ['template' => $key]) }}"
               class="group flex flex-col rounded-3xl border border-line bg-white p-5 shadow-sm transition hover:border-aron-300 hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-aron-100">
                <div class="mb-4 flex h-28 items-end gap-2 overflow-hidden rounded-2xl p-4" style="background:linear-gradient(135deg,{{ array_values($pal)[0] ?? '#2F4FF5' }},{{ array_values($pal)[1] ?? '#2F4FF5' }})" aria-hidden="true">
                    @foreach ($pal as $hex)<i class="size-7 rounded-full border-2 border-white/70" style="background:{{ $hex }}"></i>@endforeach
                </div>
                <h2 class="text-base font-extrabold text-ink">{{ $t['label'] ?? $key }}</h2>
                <p class="mt-1.5 flex-1 text-sm leading-7 text-slate-500">{{ $t['description'] ?? '' }}</p>
                <p class="mt-3 text-xs font-bold text-slate-400">بخش‌ها: {{ collect($t['sections'] ?? [])->pluck('label')->implode('، ') }}</p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-aron-700">ساخت صفحه با این قالب <i class="ph ph-arrow-left"></i></span>
            </a>
        @endforeach
    </div>
</x-admin.layout>
