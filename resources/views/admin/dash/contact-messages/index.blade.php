@php
    $tone = ['new' => 'brand', 'reviewed' => 'amber', 'contacted' => 'sky', 'closed' => 'mint'];
    $tabs = ['all' => ['همه', array_sum($counts->all())]] + collect($statuses)->map(fn ($l, $k) => [$l, $counts[$k] ?? 0])->all() + ['spam' => ['اسپم', $spamCount]];
@endphp
<x-admin.layout title="پیام‌های تماس" subtitle="پیام‌هایی که از فرم «تماس با ما» رسیده است" :breadcrumbs="[['label' => 'پیام‌های تماس']]">
    <div class="no-scrollbar mb-4 flex gap-2 overflow-x-auto pb-1">
        @foreach ($tabs as $key => [$label, $n])
            <a href="{{ route('panel.contact-messages.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => $q])) }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-2xl px-4 py-2 text-sm font-bold transition {{ $tab === $key ? 'bg-aron-600 text-white shadow-adm-brand' : 'bg-white text-slate-600 shadow-adm hover:text-aron-700' }}">
                {{ $label }}<span class="rounded-full bg-black/10 px-2 py-0.5 text-[11px]">{{ $n }}</span></a>
        @endforeach
    </div>
    <x-admin.card :padding="false">
        <form method="GET" class="flex flex-wrap items-end gap-3 p-4 sm:p-5">
            @if ($tab !== 'all')<input type="hidden" name="status" value="{{ $tab }}">@endif
            <div class="relative min-w-56 flex-1"><i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input type="search" name="q" value="{{ $q }}" placeholder="نام، موضوع، ایمیل یا متن پیام…" class="a-input ps-10"></div>
            <x-admin.button type="submit" variant="soft" icon="funnel">جستجو</x-admin.button>
            @if ($q)<x-admin.button :href="route('panel.contact-messages.index', array_filter(['status' => $tab === 'all' ? null : $tab]))" variant="ghost" icon="x">پاک‌کردن</x-admin.button>@endif
        </form>
        @if ($messages->isEmpty())
            <x-admin.empty-state icon="envelope-simple" title="پیامی پیدا نشد" description="پیام‌های تازه از صفحه تماس اینجا می‌آیند." />
        @else
            <div class="hidden md:block">
                <x-admin.table flush class="px-4 pb-2">
                    <x-slot:head><x-admin.th>فرستنده</x-admin.th><x-admin.th>موضوع</x-admin.th><x-admin.th>تماس</x-admin.th><x-admin.th>وضعیت</x-admin.th><x-admin.th>تاریخ</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
                    @foreach ($messages as $m)
                        <tr>
                            <x-admin.td><a href="{{ route('panel.contact-messages.show', $m) }}" class="{{ $m->status === 'new' ? 'font-extrabold text-ink' : 'font-bold text-slate-700' }}">{{ $m->name }}</a></x-admin.td>
                            <x-admin.td class="max-w-xs truncate">{{ $m->subject ?: 'بدون موضوع' }}<span class="block truncate text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($m->message, 70) }}</span></x-admin.td>
                            <x-admin.td muted><span dir="ltr">{{ $m->mobile ?: $m->email ?: '—' }}</span></x-admin.td>
                            <x-admin.td>@if ($m->is_spam)<x-admin.badge color="coral" dot>اسپم</x-admin.badge>@else<x-admin.badge :color="$tone[$m->status] ?? 'slate'" dot>{{ $m->status_label }}</x-admin.badge>@endif</x-admin.td>
                            <x-admin.td muted nowrap>{{ jdate($m->created_at)->format('Y/m/d H:i') }}</x-admin.td>
                            <x-admin.td class="text-end" nowrap>
                                <x-admin.button :href="route('panel.contact-messages.show', $m)" variant="ghost" size="sm" icon="eye" icon-only title="مشاهده" aria-label="مشاهده" />
                                <x-admin.confirm-delete :action="route('panel.contact-messages.destroy', $m)" icon-only message="این پیام حذف شود؟" />
                            </x-admin.td>
                        </tr>
                    @endforeach
                </x-admin.table>
            </div>
            <div class="divide-y divide-line/70 md:hidden">
                @foreach ($messages as $m)
                    <a href="{{ route('panel.contact-messages.show', $m) }}" class="block px-4 py-3.5">
                        <div class="flex items-center justify-between gap-2"><span class="{{ $m->status === 'new' ? 'font-extrabold' : 'font-bold' }} text-ink">{{ $m->name }}</span>
                            @if ($m->is_spam)<x-admin.badge color="coral" dot>اسپم</x-admin.badge>@else<x-admin.badge :color="$tone[$m->status] ?? 'slate'" dot>{{ $m->status_label }}</x-admin.badge>@endif</div>
                        <div class="mt-1 truncate text-sm text-slate-600">{{ $m->subject ?: \Illuminate\Support\Str::limit($m->message, 60) }}</div>
                        <div class="mt-0.5 text-[11px] text-slate-400">{{ jdate($m->created_at)->format('Y/m/d H:i') }}</div>
                    </a>
                @endforeach
            </div>
        @endif
        <x-slot:footer>{{ $messages->links('vendor.pagination.admin') }}</x-slot:footer>
    </x-admin.card>
</x-admin.layout>
