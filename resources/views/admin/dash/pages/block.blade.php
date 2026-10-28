@php
    $index = $siblings->search(fn ($b) => $b->id === $block->id);
    $prev = $index > 0 ? $siblings[$index - 1] : null;
    $next = $siblings[$index + 1] ?? null;
    $fields = $definition['fields'] ?? [];
    $preview = route('panel.pages.preview', ['page' => $page, 'embed' => 1]).'#'.$block->anchorId();
    $icons = \App\Support\ProductPageSchema::icons();
@endphp
<x-admin.layout :title="'ویرایش بلوک: '.$definition['label']" :subtitle="'صفحه‌ی «'.$page->title.'» — '.($definition['description'] ?? '')"
                :breadcrumbs="[['label' => 'صفحه‌ساز', 'url' => route('panel.pages.index', $page->is_template ? ['tab' => 'templates'] : [])], ['label' => $page->title, 'url' => route('panel.pages.builder', $page)], ['label' => $definition['label']]]">
    <x-slot:actions>
        @if ($prev)<x-admin.button :href="route('panel.pages.blocks.edit', [$page, $prev])" variant="secondary" icon="caret-right" title="بلوک قبلی">قبلی</x-admin.button>@endif
        @if ($next)<x-admin.button :href="route('panel.pages.blocks.edit', [$page, $next])" variant="secondary" title="بلوک بعدی">بعدی <i class="ph ph-caret-left"></i></x-admin.button>@endif
        <form method="POST" action="{{ route('panel.pages.blocks.reset', [$page, $block]) }}" class="inline-flex" data-confirm="محتوای این بلوک به نمونه‌ی پیش‌فرض برگردد؟ تغییرات شما پاک می‌شود.">@csrf
            <x-admin.button type="submit" variant="ghost" icon="arrow-counter-clockwise">محتوای پیش‌فرض</x-admin.button>
        </form>
    </x-slot:actions>
    <x-slot:head>
        <link rel="stylesheet" href="{{ asset('css/admin/page-builder.css') }}?v={{ @filemtime(public_path('css/admin/page-builder.css')) ?: 1 }}">
        <script src="{{ asset('js/admin/page-builder.js') }}?v={{ @filemtime(public_path('js/admin/page-builder.js')) ?: 1 }}" defer></script>
    </x-slot:head>

    @include('admin.dash.pages._tabs')
    <x-admin.media-config />

    <div class="pb-grid is-even">
        <form method="POST" action="{{ route('panel.pages.blocks.update', [$page, $block]) }}" class="min-w-0 space-y-5" data-pb-form novalidate>
            @csrf @method('PUT')
            <x-admin.card>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="anchor" label="شناسه‌ی لنگر (برای لینک و منو)" :value="$block->anchor" ltr hint="مثلاً services ← لینک: #services" />
                    <div class="flex items-end pb-1">
                        <x-admin.toggle name="is_active" label="این بلوک نمایش داده شود" :checked="old('is_active', $block->is_active)" />
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card :title="$definition['label']" :icon="substr($definition['icon'] ?? 'ph-layout', 3)">
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($fields as $field)
                        @php $wide = ! ($field['half'] ?? false); @endphp
                        <div class="min-w-0 {{ $wide ? 'sm:col-span-2' : '' }}">
                            @include('admin.dash.pages._field', ['field' => $field, 'value' => $data[$field['key']] ?? null])
                        </div>
                    @endforeach
                </div>
            </x-admin.card>

            <div class="sticky bottom-3 z-20 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-white/95 p-3 shadow-adm-pop backdrop-blur">
                <span class="flex items-center gap-2 text-xs font-bold text-slate-500"><span class="size-2 rounded-full bg-slate-300" data-pb-dirty-dot></span><span data-pb-dirty-text>بدون تغییر</span></span>
                <div class="flex flex-wrap gap-2">
                    <x-admin.button type="submit" name="after_save" value="builder" variant="secondary" icon="arrow-right">ذخیره و بازگشت</x-admin.button>
                    <x-admin.button type="submit" icon="floppy-disk">ذخیره و به‌روزرسانی پیش‌نمایش</x-admin.button>
                </div>
            </div>
        </form>

        <div class="min-w-0 pb-sticky">
            @include('admin.dash.pages._preview', ['url' => $preview])
        </div>
    </div>

    {{-- انتخاب آیکن (یک پاپ‌اور مشترک برای همه‌ی فیلدهای آیکن) --}}
    <div id="pp-icon-pop" class="pb-icon-pop fixed z-[70] hidden rounded-2xl border border-line bg-white p-3 shadow-adm-pop" role="dialog" aria-label="انتخاب آیکن">
        <button type="button" class="mb-2 flex w-full items-center gap-2 rounded-xl border border-dashed border-line px-3 py-2 text-xs font-bold text-slate-500 hover:border-aron-300 hover:text-aron-700" data-pp-icon-pick=""><i class="ph ph-prohibit text-base"></i>بدون آیکن</button>
        <div class="pb-icon-grid">
            @foreach ($icons as $ic)
                <button type="button" class="grid aspect-square place-items-center rounded-xl text-xl text-slate-600 transition hover:bg-aron-50 hover:text-aron-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-aron-400" data-pp-icon-pick="{{ $ic }}" title="{{ substr($ic, 3) }}" aria-label="{{ substr($ic, 3) }}" aria-pressed="false"><i class="ph {{ $ic }}"></i></button>
            @endforeach
        </div>
    </div>
</x-admin.layout>
