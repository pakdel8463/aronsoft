@php
    use App\Models\FormEntry;
    $fa = fn ($v) => \App\Helpers\Fa::toFa((string) $v);
    $base = array_filter(['form' => $form?->id, 'q' => $q]);
    $chips = [['', 'همه (بدون هرزنامه)', $counts->except('spam')->sum()]];
    foreach (FormEntry::STATUSES as $k => $l) { $chips[] = [$k, $l, (int) ($counts[$k] ?? 0)]; }
    $chips[] = ['starred', 'ستاره‌دار', $starred];
@endphp
<x-admin.layout :title="$form ? 'ورودی‌های فرم «'.$form->title.'»' : 'ورودی‌های فرم‌ها'" subtitle="هر بار که کسی فرمی را در سایت ارسال کند، اینجا ثبت می‌شود."
                :breadcrumbs="[['label' => 'فرم‌ساز', 'url' => route('panel.forms.index')], ['label' => 'ورودی‌ها']]">
    <x-slot:actions>
        @if ($form)
            <x-admin.button :href="route('panel.form-entries.export', array_filter(['form' => $form->id, 'status' => $status, 'q' => $q]))" variant="secondary" icon="file-arrow-down">خروجی Excel</x-admin.button>
            @can('forms.manage')<x-admin.button :href="route('panel.forms.edit', $form)" variant="secondary" icon="pencil-simple">ویرایش فرم</x-admin.button>@endcan
        @endif
    </x-slot:actions>

    <x-admin.card :padding="false">
        <div class="space-y-3 border-b border-line p-4 sm:p-5">
            <form method="GET" action="{{ route('panel.form-entries.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="form" class="a-select w-auto min-w-[12rem]" onchange="this.form.submit()" aria-label="فرم">
                    <option value="">همه‌ی فرم‌ها</option>
                    @foreach ($forms as $f)<option value="{{ $f->id }}" @selected($form?->id === $f->id)>{{ $f->title }}{{ $f->new_count ? ' ('.$fa($f->new_count).' جدید)' : '' }}</option>@endforeach
                </select>
                @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <div class="relative min-w-[12rem] flex-1">
                    <i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                    <input type="search" name="q" value="{{ $q }}" placeholder="جستجو در محتوای ورودی‌ها (نام، موبایل، …)" aria-label="جستجو" class="a-input ps-10">
                </div>
                <x-admin.button type="submit" variant="soft" icon="funnel">جستجو</x-admin.button>
            </form>
            <nav class="flex flex-wrap gap-2" aria-label="وضعیت">
                @foreach ($chips as [$key, $label, $n])
                    @php $on = $status === $key; @endphp
                    <a href="{{ route('panel.form-entries.index', $base + array_filter(['status' => $key])) }}" @if ($on) aria-current="true" @endif
                       class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-sm font-bold transition {{ $on ? 'border-aron-600 bg-aron-600 text-white shadow-adm-brand' : 'border-line bg-white text-slate-600 hover:border-aron-300 hover:text-aron-700' }}">
                        {{ $label }}<span class="rounded-full px-2 py-0.5 text-[11px] leading-none {{ $on ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $fa($n) }}</span></a>
                @endforeach
            </nav>
        </div>

        <form method="POST" action="{{ route('panel.form-entries.bulk') }}" id="bulk-form" data-confirm-delete="ورودی‌های انتخاب‌شده (و فایل‌هایشان) حذف شوند؟">
            @csrf
            <div class="flex flex-wrap items-center gap-2 border-b border-line bg-slate-50/60 px-4 py-2.5 sm:px-5">
                <span class="text-xs font-bold text-slate-500">با انتخاب‌شده‌ها:</span>
                <select name="action" class="a-select !h-9 w-auto text-sm" aria-label="عملیات">
                    @foreach (FormEntry::STATUSES as $k => $l)<option value="{{ $k }}">وضعیت: {{ $l }}</option>@endforeach
                    <option value="star">ستاره‌دار کردن</option><option value="unstar">برداشتن ستاره</option><option value="delete">حذف</option>
                </select>
                <x-admin.button type="submit" size="sm" variant="secondary">اعمال</x-admin.button>
            </div>
            <x-admin.table flush>
                <x-slot:head>
                    <x-admin.th class="w-10"><input type="checkbox" class="a-check" data-check-all aria-label="انتخاب همه"></x-admin.th>
                    <x-admin.th>ورودی</x-admin.th>
                    @foreach ($columns as $c)<x-admin.th>{{ $c['label'] }}</x-admin.th>@endforeach
                    @unless ($form)<x-admin.th>فرم</x-admin.th>@endunless
                    <x-admin.th>وضعیت</x-admin.th>
                    <x-admin.th>تاریخ</x-admin.th>
                </x-slot:head>
                @forelse ($entries as $e)
                    <tr class="{{ $e->status === 'new' ? 'bg-aron-50/40 font-bold' : '' }}">
                        <x-admin.td><input type="checkbox" name="ids[]" value="{{ $e->id }}" class="a-check" aria-label="انتخاب ورودی {{ $e->id }}"></x-admin.td>
                        <x-admin.td>
                            <a href="{{ route('panel.form-entries.show', $e) }}" class="flex items-center gap-2 text-ink hover:text-aron-700">
                                @if ($e->is_starred)<i class="ph-fill ph-star text-amber-500"></i>@endif
                                <span class="truncate">{{ $e->title() }}</span><span class="text-xs font-normal text-slate-400">#{{ $fa($e->id) }}</span>
                            </a>
                        </x-admin.td>
                        @foreach ($columns as $c)<x-admin.td muted>{{ \Illuminate\Support\Str::limit($e->display($c), 40) }}</x-admin.td>@endforeach
                        @unless ($form)<x-admin.td muted>{{ $e->form?->title }}</x-admin.td>@endunless
                        <x-admin.td><x-admin.badge :color="FormEntry::STATUS_COLORS[$e->status] ?? 'slate'" dot>{{ FormEntry::STATUSES[$e->status] ?? $e->status }}</x-admin.badge></x-admin.td>
                        <x-admin.td muted nowrap>{{ jdate($e->created_at)->format('Y/m/d H:i') }}</x-admin.td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 5 + count($columns) }}"><x-admin.empty-state icon="tray" title="ورودی‌ای پیدا نشد" :description="$q !== '' || $status ? 'فیلتر را تغییر دهید.' : 'وقتی کسی فرم را در سایت پر کند، اینجا نمایش داده می‌شود.'" /></td></tr>
                @endforelse
            </x-admin.table>
        </form>
        @if ($entries->hasPages())
            <x-slot:footer><x-admin.pagination :paginator="$entries" class="!mt-0" /></x-slot:footer>
        @endif
    </x-admin.card>

    <x-slot:scripts>
        <script>
            document.addEventListener('change', (e) => {
                if (e.target.matches('[data-check-all]')) document.querySelectorAll('#bulk-form input[name="ids[]"]').forEach((c) => { c.checked = e.target.checked; });
            });
            document.getElementById('bulk-form')?.addEventListener('submit', (e) => {
                const f = e.currentTarget;
                if (!f.querySelector('input[name="ids[]"]:checked')) { e.preventDefault(); window.adminToast && adminToast('ابتدا ورودی‌ها را انتخاب کنید.', 'warning'); return; }
                if (f.action.value === 'delete' && !confirm(f.dataset.confirmDelete)) e.preventDefault();
            });
        </script>
    </x-slot:scripts>
</x-admin.layout>
