@php
    $row = fn ($l, $v, $ltr = false) => '<div class="flex items-start justify-between gap-4 border-b border-line/70 py-3 last:border-0"><dt class="shrink-0 text-sm text-slate-500">'.e($l).'</dt><dd class="min-w-0 break-words text-sm font-bold text-ink" '.($ltr ? 'dir="ltr"' : '').'>'.e($v ?: '—').'</dd></div>';
    $flow = collect($statuses)->except('lost')->keys()->all();
    $cur = array_search($order->status, $flow);
    $source = collect([$order->utm_source, $order->utm_medium, $order->utm_campaign])->filter()->implode(' / ');
@endphp
<x-admin.layout :title="'سفارش '.$order->code" :subtitle="'ثبت‌شده در '.jdate($order->created_at)->format('Y/m/d H:i')" :breadcrumbs="[['label' => 'سفارش‌ها', 'url' => route('panel.orders.index')], ['label' => $order->code]]">
    <x-slot:actions>
        <x-admin.badge :color="$order->status_tone" dot class="!px-3.5 !py-2 !text-sm">{{ $order->status_label }}</x-admin.badge>
        <x-admin.button :href="$order->publicUrl()" variant="secondary" icon="arrow-square-out" target="_blank" rel="noopener">صفحه پیگیری</x-admin.button>
    </x-slot:actions>

    {{-- مراحل --}}
    <div class="a-card mb-6 p-4 sm:p-5">
        <ol class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            @foreach ($flow as $i => $k)
                @php($done = $order->status !== 'lost' && $cur !== false && $i <= $cur)
                <li class="flex shrink-0 items-center gap-2">
                    <span class="grid size-8 place-items-center rounded-full text-xs font-extrabold {{ $done ? 'bg-aron-600 text-white' : 'bg-slate-100 text-slate-400' }}">@if ($done && $i < $cur)<i class="ph ph-check"></i>@else{{ $i + 1 }}@endif</span>
                    <span class="text-sm font-bold {{ $done ? 'text-ink' : 'text-slate-400' }}">{{ $statuses[$k] }}</span>
                    @if (! $loop->last)<span class="mx-1 h-px w-8 {{ $done && $i < $cur ? 'bg-aron-400' : 'bg-slate-200' }}"></span>@endif
                </li>
            @endforeach
            @if ($order->status === 'lost')<li class="ms-4 shrink-0"><x-admin.badge color="coral" icon="x-circle">{{ $statuses['lost'] }}</x-admin.badge></li>@endif
        </ol>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card title="جزئیات درخواست" icon="clipboard-text">
                <dl>
                    {!! $row('نوع پروژه', $order->type_label) !!}{!! $row('بودجه', $order->budget_label) !!}{!! $row('زمان‌بندی', $order->deadline_label) !!}
                    <div class="border-b border-line/70 py-3"><dt class="mb-2 text-sm text-slate-500">امکانات</dt><dd class="flex flex-wrap gap-1.5">@forelse ($order->feature_labels as $f)<x-admin.badge color="brand">{{ $f }}</x-admin.badge>@empty<span class="text-sm font-bold">—</span>@endforelse</dd></div>
                    <div class="py-3"><dt class="mb-2 text-sm text-slate-500">توضیحات</dt><dd class="whitespace-pre-line rounded-2xl bg-slate-50 p-4 text-sm leading-7 text-slate-700">{{ $order->description ?: '—' }}</dd></div>
                </dl>
                @if ($order->attachment_path)
                    <a href="{{ route('panel.orders.attachment', $order) }}" class="mt-2 inline-flex items-center gap-3 rounded-2xl border border-line px-4 py-3 text-sm font-bold text-aron-700 hover:bg-aron-50">
                        <i class="ph ph-paperclip text-xl"></i><span>{{ $order->attachment_name ?: 'دانلود فایل پیوست' }}</span><i class="ph ph-download-simple"></i></a>
                @endif
            </x-admin.card>

            <x-admin.card title="یادداشت داخلی" icon="note-pencil" subtitle="فقط برای مدیران قابل مشاهده است.">
                <form method="POST" action="{{ route('panel.orders.note', $order) }}" class="space-y-3">@csrf @method('PATCH')
                    <x-admin.textarea name="admin_note" label="متن یادداشت" rows="4" :value="$order->admin_note" maxlength="5000" placeholder="نتیجه تماس، پیشنهاد قیمت، …" />
                    <x-admin.button type="submit" icon="floppy-disk" loading>ذخیره یادداشت</x-admin.button>
                </form>
            </x-admin.card>

            <x-admin.card title="تاریخچه" icon="clock-counter-clockwise">
                <ol class="relative space-y-5 border-s-2 border-line ps-6">
                    @foreach ($order->events as $e)
                        <li class="relative">
                            <span class="absolute -start-[1.95rem] top-0.5 grid size-5 place-items-center rounded-full bg-white ring-2 ring-aron-200"><span class="size-2 rounded-full {{ $e->type === 'status' ? 'bg-aron-500' : 'bg-amber-400' }}"></span></span>
                            <div class="text-sm font-bold text-ink">@if ($e->type === 'status')تغییر وضعیت از «{{ $statuses[$e->from] ?? $e->from }}» به «{{ $statuses[$e->to] ?? $e->to }}»@else یادداشت ثبت شد@endif</div>
                            @if ($e->note)<p class="mt-1 whitespace-pre-line text-xs leading-6 text-slate-500">{{ $e->note }}</p>@endif
                            <div class="mt-1 text-[11px] text-slate-400">{{ jdate($e->created_at)->format('Y/m/d H:i') }}@if ($e->user) · {{ $e->user->name }}@endif</div>
                        </li>
                    @endforeach
                    @if ($order->consented_at)<li class="relative"><span class="absolute -start-[1.95rem] top-0.5 grid size-5 place-items-center rounded-full bg-white ring-2 ring-mint-200"><span class="size-2 rounded-full bg-mint-500"></span></span>
                        <div class="text-sm font-bold text-ink">رضایت از قوانین حریم خصوصی</div><div class="mt-1 text-[11px] text-slate-400">{{ jdate($order->consented_at)->format('Y/m/d H:i') }}</div></li>@endif
                    <li class="relative"><span class="absolute -start-[1.95rem] top-0.5 grid size-5 place-items-center rounded-full bg-white ring-2 ring-aron-200"><span class="size-2 rounded-full bg-slate-400"></span></span>
                        <div class="text-sm font-bold text-ink">ثبت درخواست توسط مشتری</div><div class="mt-1 text-[11px] text-slate-400">{{ jdate($order->created_at)->format('Y/m/d H:i') }}</div></li>
                </ol>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card title="تغییر وضعیت" icon="arrows-clockwise">
                <form method="POST" action="{{ route('panel.orders.status', $order) }}" class="space-y-3">@csrf @method('PATCH')
                    <x-admin.select name="status" label="وضعیت جدید" :options="$statuses" :selected="$order->status" />
                    <x-admin.button type="submit" class="w-full" icon="check" loading>ثبت وضعیت</x-admin.button>
                </form>
            </x-admin.card>
            <x-admin.card title="مشتری" icon="user">
                <dl>{!! $row('نام', $order->name) !!}{!! $row('موبایل', $order->mobile, true) !!}{!! $row('ایمیل', $order->email, true) !!}{!! $row('زمان تماس', $order->contact_time_label) !!}
                    {!! $row('کاربر سایت', $order->user?->name) !!}{!! $row('منبع', $source, true) !!}{!! $row('IP', $order->ip, true) !!}</dl>
                <div class="mt-4 flex flex-wrap gap-2">
                    <x-admin.button :href="'tel:'.$order->mobile" variant="secondary" size="sm" icon="phone">تماس</x-admin.button>
                    @if ($order->email)<x-admin.button :href="'mailto:'.$order->email" variant="secondary" size="sm" icon="envelope-simple">ایمیل</x-admin.button>@endif
                    @if ($order->lead && Route::has('panel.leads.show'))<x-admin.button :href="route('panel.leads.show', $order->lead)" variant="ghost" size="sm" icon="target">لید مرتبط</x-admin.button>@endif
                </div>
            </x-admin.card>
            <x-admin.card>
                <x-admin.confirm-delete :action="route('panel.orders.destroy', $order)" message="این سفارش و فایل پیوست آن برای همیشه حذف شود؟" label="حذف سفارش" variant="danger-soft" size="md" class="w-full justify-center" />
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
