@php($tone = ['confirmed' => 'mint', 'pending' => 'amber', 'unsubscribed' => 'slate'])
<x-admin.layout title="خبرنامه" subtitle="اعضای خبرنامه با تأیید دو مرحله‌ای" :breadcrumbs="[['label' => 'خبرنامه']]">
    <x-slot:actions>
        <x-admin.button :href="route('panel.newsletter.export', array_filter($filters))" variant="secondary" icon="download-simple">خروجی CSV</x-admin.button>
        <x-admin.button :href="route('panel.newsletter.compose')" icon="paper-plane-tilt">ارسال خبرنامه</x-admin.button>
    </x-slot:actions>
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.stat-card label="همه" :value="number_format($counts['all'])" icon="envelope-simple" color="brand" />
        <x-admin.stat-card label="تأییدشده" :value="number_format($counts['confirmed'])" icon="seal-check" color="mint" />
        <x-admin.stat-card label="منتظر تأیید" :value="number_format($counts['pending'])" icon="hourglass" color="amber" />
        <x-admin.stat-card label="لغو عضویت" :value="number_format($counts['unsubscribed'])" icon="user-minus" color="coral" />
    </div>
    <x-admin.card :padding="false">
        <form method="GET" class="flex flex-wrap items-end gap-3 p-4 sm:p-5">
            <div class="relative min-w-56 flex-1"><i class="ph ph-magnifying-glass pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="جستجوی ایمیل…" class="a-input ps-10" dir="ltr"></div>
            <select name="status" class="a-select w-full sm:w-44"><option value="">همه وضعیت‌ها</option>@foreach ($states as $k => $l)<option value="{{ $k }}" @selected($filters['status'] === $k)>{{ $l }}</option>@endforeach</select>
            <x-admin.button type="submit" variant="soft" icon="funnel">اعمال</x-admin.button>
            @if (array_filter($filters))<x-admin.button :href="route('panel.newsletter.index')" variant="ghost" icon="x">پاک‌کردن</x-admin.button>@endif
        </form>
        @if ($subscribers->isEmpty())
            <x-admin.empty-state icon="megaphone" title="عضوی پیدا نشد" description="اعضا از فرم خبرنامه سایت ثبت‌نام می‌کنند و پس از تأیید ایمیل فعال می‌شوند." />
        @else
            <x-admin.table flush class="px-4 pb-2">
                <x-slot:head><x-admin.th>ایمیل</x-admin.th><x-admin.th>وضعیت</x-admin.th><x-admin.th class="hidden sm:table-cell">تاریخ عضویت</x-admin.th><x-admin.th></x-admin.th></x-slot:head>
                @foreach ($subscribers as $s)
                    <tr>
                        <x-admin.td><span dir="ltr" class="font-bold text-ink">{{ $s->email }}</span></x-admin.td>
                        <x-admin.td><x-admin.badge :color="$tone[$s->state]" dot>{{ $states[$s->state] }}</x-admin.badge></x-admin.td>
                        <x-admin.td muted nowrap class="hidden sm:table-cell">{{ jdate($s->created_at)->format('Y/m/d') }}</x-admin.td>
                        <x-admin.td class="text-end" nowrap>
                            <form method="POST" action="{{ route('panel.newsletter.toggle', $s) }}" class="inline-flex">@csrf
                                <x-admin.button type="submit" variant="ghost" size="sm" :icon="$s->is_active ? 'pause' : 'play'" icon-only :title="$s->is_active ? 'غیرفعال‌سازی' : 'فعال‌سازی'" :aria-label="$s->is_active ? 'غیرفعال‌سازی' : 'فعال‌سازی'" /></form>
                            <x-admin.confirm-delete :action="route('panel.newsletter.destroy', $s)" icon-only message="این ایمیل از لیست حذف شود؟" />
                        </x-admin.td>
                    </tr>
                @endforeach
            </x-admin.table>
        @endif
        <x-slot:footer>{{ $subscribers->links('vendor.pagination.admin') }}</x-slot:footer>
    </x-admin.card>
</x-admin.layout>
