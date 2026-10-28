@php
    $fa = fn ($v) => \App\Helpers\Fa::toFa((string) $v);
    $isTpl = $tab === 'templates';
@endphp
<x-admin.layout title="صفحه‌ساز" subtitle="صفحه‌های سایت را با بلوک‌های آماده بسازید؛ بدون نیاز به کدنویسی."
                :breadcrumbs="[['label' => 'صفحه‌ساز']]">
    <x-slot:head><link rel="stylesheet" href="{{ asset('css/admin/page-builder.css') }}?v={{ @filemtime(public_path('css/admin/page-builder.css')) ?: 1 }}"></x-slot:head>
    <x-slot:actions>
        @if ($isTpl)
            <x-admin.button icon="plus" data-modal-open="new-template">قالب جدید</x-admin.button>
        @else
            <x-admin.button icon="plus" data-modal-open="new-page">صفحه‌ی جدید</x-admin.button>
        @endif
    </x-slot:actions>

    <x-admin.card :padding="false">
        <div class="space-y-3 border-b border-line p-4 sm:p-5">
            <nav class="flex flex-wrap gap-2" aria-label="نوع">
                @foreach (['pages' => ['صفحه‌ها', 'browser'], 'templates' => ['قالب‌ها', 'stack']] as $key => [$label, $icon])
                    @php $on = $tab === $key; @endphp
                    <a href="{{ route('panel.pages.index', $key === 'pages' ? [] : ['tab' => $key]) }}" @if ($on) aria-current="true" @endif
                       class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-sm font-bold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-aron-100 {{ $on ? 'border-aron-600 bg-aron-600 text-white shadow-adm-brand' : 'border-line bg-white text-slate-600 hover:border-aron-300 hover:text-aron-700' }}">
                        <i class="ph ph-{{ $icon }}"></i>{{ $label }}
                        <span class="rounded-full px-2 py-0.5 text-[11px] leading-none {{ $on ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $fa($counts[$key]) }}</span>
                    </a>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('panel.pages.index') }}" class="flex flex-wrap items-center gap-3">
                @if ($isTpl)<input type="hidden" name="tab" value="templates">@endif
                <div class="relative min-w-[12rem] flex-1">
                    <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                    <input type="search" name="q" value="{{ $q }}" placeholder="جستجوی عنوان یا آدرس…" aria-label="جستجو" class="a-input ps-10">
                </div>
                <x-admin.button type="submit" variant="soft" icon="funnel">جستجو</x-admin.button>
                @if ($q !== '')<x-admin.button :href="route('panel.pages.index', $isTpl ? ['tab' => 'templates'] : [])" variant="ghost">پاک کردن</x-admin.button>@endif
            </form>
        </div>

        <x-admin.table flush>
            <x-slot:head>
                <x-admin.th>{{ $isTpl ? 'قالب' : 'صفحه' }}</x-admin.th>
                @unless ($isTpl)<x-admin.th>آدرس</x-admin.th><x-admin.th>وضعیت</x-admin.th>@endunless
                <x-admin.th>بلوک‌ها</x-admin.th>
                <x-admin.th>آخرین ویرایش</x-admin.th>
                <x-admin.th></x-admin.th>
            </x-slot:head>
            @forelse ($pages as $p)
                <tr>
                    <x-admin.td>
                        <div class="flex min-w-[14rem] items-center gap-3">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl {{ $p->is_home ? 'bg-aron-600 text-white shadow-adm-brand' : 'bg-aron-50 text-aron-600' }} text-xl">
                                <i class="ph {{ $p->is_home ? 'ph-house' : ($isTpl ? 'ph-stack' : 'ph-browser') }}"></i>
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('panel.pages.builder', $p) }}" class="block truncate font-extrabold text-ink hover:text-aron-700">{{ $p->title }}</a>
                                @if ($p->is_home)<span class="block text-[11px] font-bold text-aron-600">صفحه‌ی اصلی سایت</span>@endif
                                @if ($isTpl && $p->description)<span class="block max-w-md truncate text-xs text-slate-400">{{ $p->description }}</span>@endif
                            </div>
                        </div>
                    </x-admin.td>
                    @unless ($isTpl)
                        <x-admin.td nowrap>
                            @if ($p->is_active)
                                <a href="{{ $p->url() }}" target="_blank" rel="noopener" dir="ltr" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-aron-700">{{ $p->is_home ? '/' : '/'.$p->slug }}<i class="ph ph-arrow-square-out"></i></a>
                            @else
                                <span dir="ltr" class="text-xs font-bold text-slate-400">/{{ $p->slug }}</span>
                            @endif
                        </x-admin.td>
                        <x-admin.td>
                            <form method="POST" action="{{ route('panel.pages.publish', $p) }}" class="inline-flex">@csrf
                                <button type="submit" class="rounded-full focus:outline-none focus-visible:ring-4 focus-visible:ring-aron-100" title="{{ $p->is_active ? 'بازگشت به پیش‌نویس' : 'انتشار' }}">
                                    <x-admin.badge :color="$p->is_active ? 'mint' : 'slate'" dot>{{ $p->is_active ? 'منتشرشده' : 'پیش‌نویس' }}</x-admin.badge>
                                </button>
                            </form>
                        </x-admin.td>
                    @endunless
                    <x-admin.td muted nowrap>{{ $fa($p->blocks_count) }}</x-admin.td>
                    <x-admin.td muted nowrap>{{ $p->updated_at ? jdate($p->updated_at)->format('Y/m/d H:i') : '—' }}</x-admin.td>
                    <x-admin.td class="text-end" nowrap>
                        <div class="flex items-center justify-end gap-1">
                            <x-admin.button :href="route('panel.pages.builder', $p)" size="sm" icon="layout">صفحه‌ساز</x-admin.button>
                            <x-admin.button :href="route('panel.pages.edit', $p)" variant="ghost" size="sm" icon="gear-six" icon-only title="تنظیمات و سئو" aria-label="تنظیمات و سئو" />
                            <x-admin.button :href="$p->previewUrl()" variant="ghost" size="sm" icon="eye" icon-only target="_blank" title="پیش‌نمایش" aria-label="پیش‌نمایش" />
                            @unless ($isTpl)
                                <form method="POST" action="{{ route('panel.pages.home', $p) }}" class="inline-flex" @unless ($p->is_home) data-confirm="«{{ $p->title }}» به‌جای صفحه‌ی اصلی فعلی سایت نمایش داده شود؟ (و منتشر می‌شود)" @endunless>@csrf
                                    <x-admin.button type="submit" :variant="$p->is_home ? 'soft' : 'ghost'" size="sm" icon="house" icon-only :title="$p->is_home ? 'برداشتن از صفحه‌ی اصلی' : 'صفحه‌ی اصلی سایت شود'" :aria-label="$p->is_home ? 'برداشتن از صفحه‌ی اصلی' : 'صفحه‌ی اصلی سایت شود'" />
                                </form>
                                <form method="POST" action="{{ route('panel.pages.template', $p) }}" class="inline-flex">@csrf
                                    <x-admin.button type="submit" variant="ghost" size="sm" icon="stack" icon-only title="ذخیره به‌عنوان قالب" aria-label="ذخیره به‌عنوان قالب" />
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('panel.pages.duplicate', $p) }}" class="inline-flex">@csrf
                                <x-admin.button type="submit" variant="ghost" size="sm" icon="copy-simple" icon-only title="تکثیر" aria-label="تکثیر" />
                            </form>
                            <x-admin.confirm-delete :action="route('panel.pages.destroy', $p)" message="«{{ $p->title }}» و همه‌ی بلوک‌هایش حذف شود؟" icon-only size="sm" />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr><td colspan="6">
                    <x-admin.empty-state :icon="$isTpl ? 'stack' : 'browser'" :title="$q !== '' ? 'نتیجه‌ای پیدا نشد' : ($isTpl ? 'هنوز قالبی نیست' : 'هنوز صفحه‌ای ساخته نشده')"
                        :description="$q !== '' ? 'عبارت جستجو را تغییر دهید.' : ($isTpl ? 'هر صفحه‌ای را می‌توانید «به‌عنوان قالب» ذخیره کنید.' : 'از یکی از قالب‌های آماده شروع کنید؛ بعد بلوک‌ها را اضافه، ویرایش و جابه‌جا کنید.')">
                        @if ($q === '')
                            <x-admin.button icon="plus" :data-modal-open="$isTpl ? 'new-template' : 'new-page'">{{ $isTpl ? 'قالب جدید' : 'ساخت اولین صفحه' }}</x-admin.button>
                        @endif
                    </x-admin.empty-state>
                </td></tr>
            @endforelse
        </x-admin.table>
        @if ($pages->hasPages())
            <x-slot:footer><x-admin.pagination :paginator="$pages" class="!mt-0" /></x-slot:footer>
        @endif
    </x-admin.card>

    @unless ($isTpl)
        <p class="mt-4 text-xs leading-6 text-slate-500"><i class="ph ph-info align-middle"></i>
            صفحه‌ها در آدرس <span dir="ltr">aron-soft.com/آدرس-صفحه</span> نمایش داده می‌شوند. اگر صفحه‌ای را «صفحه‌ی اصلی سایت» کنید، به‌جای صفحه‌ی اصلی فعلی نمایش داده می‌شود و هر وقت بخواهید برمی‌گردد.</p>
    @endunless

    {{-- ساخت صفحه / قالب --}}
    @foreach (['new-page' => false, 'new-template' => true] as $modal => $asTemplate)
        <x-admin.modal :name="$modal" :title="$asTemplate ? 'قالب جدید' : 'صفحه‌ی جدید'" max-width="2xl">
            <form method="POST" action="{{ route('panel.pages.store') }}" id="{{ $modal }}-form" class="space-y-5">
                @csrf
                @if ($asTemplate)<input type="hidden" name="as_template" value="1">@endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="title" label="عنوان" required :placeholder="$asTemplate ? 'مثلاً: لندینگ کمپین' : 'مثلاً: طراحی سایت فروشگاهی'" />
                    @unless ($asTemplate)
                        <x-admin.input name="slug" label="آدرس (اختیاری)" hint="خالی بماند تا از عنوان ساخته شود." ltr placeholder="website-design" />
                    @endunless
                </div>
                <fieldset>
                    <legend class="a-label">شروع از</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="pb-choice flex cursor-pointer items-start gap-3 rounded-2xl border border-line p-4 transition hover:border-aron-300">
                            <input type="radio" name="from" value="" class="a-check mt-1" checked>
                            <span><span class="block font-extrabold text-ink">صفحه‌ی خالی</span><span class="block text-xs leading-5 text-slate-500">خودتان بلوک‌ها را اضافه می‌کنید.</span></span>
                        </label>
                        @foreach ($templates as $t)
                            <label class="pb-choice flex cursor-pointer items-start gap-3 rounded-2xl border border-line p-4 transition hover:border-aron-300">
                                <input type="radio" name="from" value="{{ $t->id }}" class="a-check mt-1">
                                <span class="min-w-0"><span class="block font-extrabold text-ink">{{ $t->title }} <span class="text-xs font-bold text-slate-400">({{ $fa($t->blocks_count) }} بلوک)</span></span>
                                    @if ($t->description)<span class="block text-xs leading-5 text-slate-500">{{ $t->description }}</span>@endif</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </form>
            <x-slot:footer>
                <x-admin.button variant="secondary" data-modal-close>انصراف</x-admin.button>
                <x-admin.button type="submit" :form="$modal.'-form'" icon="arrow-left" data-loading-on-submit>ساخت و رفتن به صفحه‌ساز</x-admin.button>
            </x-slot:footer>
        </x-admin.modal>
    @endforeach
    @if ($errors->any() && old('title') !== null)
        <x-slot:scripts><script>document.addEventListener('DOMContentLoaded', () => window.adminModal && adminModal.open(@json(old('as_template') ? 'new-template' : 'new-page')));</script></x-slot:scripts>
    @endif
</x-admin.layout>
