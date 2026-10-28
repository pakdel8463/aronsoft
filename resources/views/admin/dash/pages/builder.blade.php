@php
    $fa = fn ($v) => \App\Helpers\Fa::toFa((string) $v);
    $preview = route('panel.pages.preview', ['page' => $page, 'embed' => 1]);
@endphp
<x-admin.layout :title="($page->is_template ? 'قالب: ' : 'صفحه‌ساز: ').$page->title"
                :subtitle="$page->is_template ? 'قالب قابل ویرایش؛ صفحه‌های جدید را می‌توانید از روی آن بسازید.' : ($page->is_home ? 'صفحه‌ی اصلی سایت' : 'aron-soft.com/'.$page->slug)"
                :breadcrumbs="[['label' => 'صفحه‌ساز', 'url' => route('panel.pages.index', $page->is_template ? ['tab' => 'templates'] : [])], ['label' => $page->title]]">
    <x-slot:actions>
        @unless ($page->is_template)
            <form method="POST" action="{{ route('panel.pages.template', $page) }}" class="inline-flex">@csrf
                <x-admin.button type="submit" variant="secondary" icon="stack">ذخیره به‌عنوان قالب</x-admin.button>
            </form>
        @endunless
        <x-admin.button :href="$page->is_active && ! $page->is_template ? $page->url() : $page->previewUrl()" variant="secondary" icon="arrow-square-out" target="_blank">{{ $page->is_active && ! $page->is_template ? 'مشاهده در سایت' : 'پیش‌نمایش' }}</x-admin.button>
    </x-slot:actions>
    <x-slot:head>
        <link rel="stylesheet" href="{{ asset('css/admin/page-builder.css') }}?v={{ @filemtime(public_path('css/admin/page-builder.css')) ?: 1 }}">
        <script src="{{ asset('vendor/sortable/Sortable.min.js') }}" defer></script>
        <script src="{{ asset('js/admin/page-builder.js') }}?v={{ @filemtime(public_path('js/admin/page-builder.js')) ?: 1 }}" defer></script>
    </x-slot:head>

    @include('admin.dash.pages._tabs')

    <div class="pb-grid" data-pb-builder data-sort-url="{{ route('panel.pages.blocks.sort', $page) }}">
        <div class="min-w-0 space-y-4">
            <x-admin.card :padding="false">
                <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3">
                    <p class="text-sm font-extrabold text-ink">بلوک‌های صفحه <span class="font-bold text-slate-400">({{ $fa($blocks->count()) }})</span></p>
                    <x-admin.button size="sm" icon="plus" data-pb-add data-after="">افزودن بلوک</x-admin.button>
                </div>
                @if ($blocks->isEmpty())
                    <x-admin.empty-state icon="layout" title="این صفحه هنوز بلوکی ندارد" description="از کتابخانه یک بلوک اضافه کنید؛ با محتوای نمونه اضافه می‌شود و بعد ویرایشش می‌کنید.">
                        <x-admin.button icon="plus" data-pb-add data-after="">اولین بلوک را اضافه کنید</x-admin.button>
                    </x-admin.empty-state>
                @endif
                <ul class="divide-y divide-line" data-pb-list>
                    @foreach ($blocks as $block)
                        @php $known = $block->isKnown(); $summary = $known ? $block->summary() : ''; @endphp
                        <li data-id="{{ $block->id }}" class="group relative bg-white">
                            <div class="flex items-center gap-3 px-3 py-3 sm:px-4">
                                <span class="grid h-9 w-6 shrink-0 cursor-grab place-items-center text-slate-300 hover:text-slate-500 active:cursor-grabbing" data-pb-handle title="بکشید تا جابه‌جا شود" aria-hidden="true"><i class="ph ph-dots-six-vertical text-xl"></i></span>
                                <span class="grid size-11 shrink-0 place-items-center rounded-xl text-xl {{ $block->is_active ? 'bg-aron-50 text-aron-600' : 'bg-slate-100 text-slate-400' }}"><i class="ph {{ $block->icon() }}"></i></span>
                                <a href="{{ $known ? route('panel.pages.blocks.edit', [$page, $block]) : '#' }}" class="min-w-0 flex-1">
                                    <span class="block truncate font-extrabold {{ $block->is_active ? 'text-ink' : 'pb-off text-slate-400' }}">{{ $known ? $block->label() : 'بلوک ناشناخته ('.$block->type.')' }}</span>
                                    <span class="block truncate text-xs text-slate-500"><span dir="ltr" class="text-slate-400">#{{ $block->anchorId() }}</span>@if ($summary !== '') · {{ $summary }}@endif</span>
                                </a>
                                <div class="flex shrink-0 items-center gap-0.5">
                                    <form method="POST" action="{{ route('panel.pages.blocks.toggle', [$page, $block]) }}" class="inline-flex">@csrf @method('PATCH')
                                        <x-admin.button type="submit" variant="ghost" size="sm" :icon="$block->is_active ? 'eye' : 'eye-slash'" icon-only :title="$block->is_active ? 'پنهان کردن' : 'نمایش'" :aria-label="$block->is_active ? 'پنهان کردن' : 'نمایش'" />
                                    </form>
                                    <form method="POST" action="{{ route('panel.pages.blocks.duplicate', [$page, $block]) }}" class="inline-flex">@csrf
                                        <x-admin.button type="submit" variant="ghost" size="sm" icon="copy-simple" icon-only title="کپی" aria-label="کپی" />
                                    </form>
                                    <x-admin.confirm-delete :action="route('panel.pages.blocks.destroy', [$page, $block])" message="این بلوک حذف شود؟" icon-only size="sm" />
                                    @if ($known)
                                        <x-admin.button :href="route('panel.pages.blocks.edit', [$page, $block])" size="sm" icon="pencil-simple" class="ms-1">ویرایش</x-admin.button>
                                    @endif
                                </div>
                            </div>
                            <button type="button" data-pb-add data-after="{{ $block->id }}" class="pb-insert items-center gap-1 rounded-full bg-aron-600 px-3 py-1 text-[11px] font-bold text-white shadow-adm-brand group-hover:flex focus:flex">
                                <i class="ph ph-plus"></i> افزودن بلوک بعد از این
                            </button>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
            <p class="text-xs leading-6 text-slate-500"><i class="ph ph-info align-middle"></i>
                بلوک‌ها را با دستگیره‌ی <i class="ph ph-dots-six-vertical align-middle"></i> بکشید تا ترتیبشان عوض شود (خودکار ذخیره می‌شود). منوی بالای سایت از
                <a href="{{ route('panel.menus.index') }}" class="font-bold text-aron-700 hover:underline">منوها</a> ویرایش می‌شود؛ برای لینک به یک بلوک از آدرس <span dir="ltr">/آدرس-صفحه#شناسه</span> استفاده کنید.</p>
        </div>

        <div class="min-w-0 pb-sticky">
            @include('admin.dash.pages._preview', ['url' => $preview])
        </div>
    </div>

    {{-- کتابخانه‌ی بلوک‌ها --}}
    <div data-modal="pb-library" class="fixed inset-0 z-[90] hidden items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-label="کتابخانه‌ی بلوک‌ها">
        <div data-modal-close class="absolute inset-0 bg-rail/60 backdrop-blur-[2px]"></div>
        <div data-modal-panel class="pb-lib-panel relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-[1.75rem] bg-white shadow-adm-pop sm:rounded-[1.5rem]">
            <header class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
                <div>
                    <h3 class="text-base font-extrabold text-ink">کتابخانه‌ی بلوک‌ها</h3>
                    <p class="text-xs text-slate-500">بلوک با محتوای نمونه اضافه می‌شود و بعد ویرایشش می‌کنید.</p>
                </div>
                <button type="button" data-modal-close class="grid size-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="بستن"><i class="ph ph-x text-lg"></i></button>
            </header>
            <div class="pb-lib-body">
                <nav class="pb-lib-nav" aria-label="دسته‌ها">
                    @foreach ($library as $cat => $items)
                        <button type="button" data-pb-cat="{{ $cat }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                class="pb-pressed shrink-0 whitespace-nowrap rounded-xl px-3 py-2 text-start text-sm font-bold text-slate-600 transition hover:bg-slate-100">
                            {{ $categories[$cat] ?? $cat }} <span class="opacity-60">({{ $fa(count($items)) }})</span>
                        </button>
                    @endforeach
                </nav>
                <div class="min-h-0 flex-1 overflow-y-auto p-4">
                    @foreach ($library as $cat => $items)
                        <div data-pb-cat-panel="{{ $cat }}" class="pb-lib-items" @unless ($loop->first) hidden @endunless>
                            @foreach ($items as $type => $def)
                                <form method="POST" action="{{ route('panel.pages.blocks.store', $page) }}">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <input type="hidden" name="after" value="" data-pb-after>
                                    <button type="submit" class="flex h-full w-full items-start gap-3 rounded-2xl border border-line p-4 text-start transition hover:border-aron-400 hover:bg-aron-50/50 hover:shadow-adm focus:outline-none focus-visible:ring-4 focus-visible:ring-aron-100">
                                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-aron-50 text-2xl text-aron-600"><i class="ph {{ $def['icon'] ?? 'ph-layout' }}"></i></span>
                                        <span class="min-w-0">
                                            <span class="block font-extrabold text-ink">{{ $def['label'] }}</span>
                                            <span class="mt-0.5 block text-xs leading-5 text-slate-500">{{ $def['description'] ?? '' }}</span>
                                        </span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
