@php $fa = fn ($v) => \App\Helpers\Fa::toFa((string) $v); @endphp
<x-admin.layout title="فرم‌ساز" subtitle="فرم‌های دلخواه بسازید و در صفحه‌ها، نوشته‌ها و بلوک‌ها قرار دهید؛ ورودی‌ها در پنل ثبت می‌شوند."
                :breadcrumbs="[['label' => 'فرم‌ساز']]">
    <x-slot:head><link rel="stylesheet" href="{{ asset('css/admin/page-builder.css') }}?v={{ @filemtime(public_path('css/admin/page-builder.css')) ?: 1 }}"></x-slot:head>
    <x-slot:actions>
        @can('forms.entries')<x-admin.button :href="route('panel.form-entries.index')" variant="secondary" icon="tray">همه‌ی ورودی‌ها</x-admin.button>@endcan
        <x-admin.button icon="plus" data-modal-open="new-form">فرم جدید</x-admin.button>
    </x-slot:actions>

    <form method="GET" action="{{ route('panel.forms.index') }}" class="mb-5 flex flex-wrap items-center gap-3">
        <div class="relative min-w-[12rem] flex-1">
            <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
            <input type="search" name="q" value="{{ $q }}" placeholder="جستجوی عنوان فرم…" aria-label="جستجو" class="a-input ps-10">
        </div>
        <x-admin.button type="submit" variant="soft" icon="funnel">جستجو</x-admin.button>
    </form>

    @if ($forms->isEmpty())
        <x-admin.card>
            <x-admin.empty-state icon="list-checks" :title="$q !== '' ? 'فرمی پیدا نشد' : 'هنوز فرمی ساخته نشده'" description="از یکی از قالب‌ها (تماس، درخواست پروژه، استخدام) شروع کنید.">
                <x-admin.button icon="plus" data-modal-open="new-form">ساخت اولین فرم</x-admin.button>
            </x-admin.empty-state>
        </x-admin.card>
    @else
        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
            @foreach ($forms as $form)
                <x-admin.card class="flex flex-col">
                    <div class="flex items-start gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-aron-50 text-xl text-aron-600"><i class="ph ph-list-checks"></i></span>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('panel.forms.edit', $form) }}" class="block truncate text-base font-extrabold text-ink hover:text-aron-700">{{ $form->title }}</a>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $fa(count($form->fields ?? [])) }} فیلد · ویرایش {{ jdate($form->updated_at)->ago() }}</p>
                        </div>
                        <x-admin.badge :color="$form->is_active ? 'mint' : 'slate'" dot>{{ $form->is_active ? 'فعال' : 'غیرفعال' }}</x-admin.badge>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                        <a href="{{ route('panel.form-entries.index', ['form' => $form->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-50 px-3 py-1.5 font-bold text-slate-600 hover:bg-aron-50 hover:text-aron-700">
                            <i class="ph ph-tray"></i>{{ $fa($form->entries_count) }} ورودی
                            @if ($form->new_entries_count)<span class="rounded-full bg-coral-500 px-2 text-[11px] text-white">{{ $fa($form->new_entries_count) }} جدید</span>@endif
                        </a>
                        <button type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-500 hover:bg-aron-50 hover:text-aron-700" data-copy="{{ $form->shortcode() }}" title="کپی شورت‌کد برای قرار دادن در نوشته‌ها">
                            <i class="ph ph-copy-simple"></i><span dir="ltr">{{ $form->shortcode() }}</span>
                        </button>
                    </div>
                    <div class="mt-auto flex flex-wrap items-center gap-1 border-t border-line pt-3" style="margin-top:1rem">
                        <x-admin.button :href="route('panel.forms.edit', $form)" size="sm" icon="pencil-simple">ویرایش</x-admin.button>
                        <x-admin.button :href="route('panel.forms.preview', $form)" variant="ghost" size="sm" icon="eye" target="_blank">پیش‌نمایش</x-admin.button>
                        <form method="POST" action="{{ route('panel.forms.duplicate', $form) }}" class="inline-flex">@csrf
                            <x-admin.button type="submit" variant="ghost" size="sm" icon="copy-simple" icon-only title="کپی فرم" aria-label="کپی فرم" />
                        </form>
                        <span class="ms-auto"><x-admin.confirm-delete :action="route('panel.forms.destroy', $form)" :message="'فرم «'.$form->title.'» و همه‌ی ورودی‌ها و فایل‌هایش حذف شود؟'" icon-only size="sm" /></span>
                    </div>
                </x-admin.card>
            @endforeach
        </div>
    @endif

    <x-admin.modal name="new-form" title="فرم جدید" max-width="2xl">
        <form method="POST" action="{{ route('panel.forms.store') }}" id="new-form-form" class="space-y-5">
            @csrf
            <x-admin.input name="title" label="عنوان فرم" required placeholder="مثلاً: درخواست مشاوره" />
            <fieldset>
                <legend class="a-label">شروع از</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($templates as $key => $t)
                        <label class="pb-choice flex cursor-pointer items-start gap-3 rounded-2xl border border-line p-4 transition hover:border-aron-300">
                            <input type="radio" name="template" value="{{ $key }}" class="a-check mt-1" @checked($loop->first)>
                            <span class="min-w-0"><span class="flex items-center gap-1.5 font-extrabold text-ink"><i class="ph ph-{{ $t['icon'] }} text-aron-600"></i>{{ $t['label'] }}</span>
                                <span class="block text-xs leading-5 text-slate-500">{{ $t['description'] }}</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </form>
        <x-slot:footer>
            <x-admin.button variant="secondary" data-modal-close>انصراف</x-admin.button>
            <x-admin.button type="submit" form="new-form-form" icon="arrow-left" data-loading-on-submit>ساخت و رفتن به فرم‌ساز</x-admin.button>
        </x-slot:footer>
    </x-admin.modal>

    <x-slot:scripts>
        <script>
            document.addEventListener('click', (e) => {
                const b = e.target.closest('[data-copy]'); if (!b) return;
                const t = b.dataset.copy;
                (navigator.clipboard && window.isSecureContext ? navigator.clipboard.writeText(t) : Promise.reject()).then(
                    () => window.adminToast && adminToast('شورت‌کد کپی شد؛ آن را در متن نوشته یا بلوک «متن» بگذارید.'),
                    () => { window.prompt('شورت‌کد را کپی کنید:', t); });
            });
            @if ($errors->any() && old('title') !== null)
                document.addEventListener('DOMContentLoaded', () => window.adminModal && adminModal.open('new-form'));
            @endif
        </script>
    </x-slot:scripts>
</x-admin.layout>
