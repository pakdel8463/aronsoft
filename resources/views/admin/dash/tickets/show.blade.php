@php
    use App\Models\Ticket;
    $fa = fn ($v) => \App\Helpers\Fa::toFa($v);
    $sla = $ticket->sla();
@endphp
<x-admin.layout :title="$ticket->number" :subtitle="$ticket->subject" :breadcrumbs="[['label' => 'تیکت‌ها', 'url' => route('panel.tickets.index')], ['label' => $ticket->number]]">
    <x-slot:actions>
        <x-admin.badge :color="$ticket->status_color" dot>{{ $ticket->status_label }}</x-admin.badge>
        <x-admin.badge :color="$ticket->priority_color" icon="flag">{{ $ticket->priority_label }}</x-admin.badge>
        @if($sla)<x-admin.badge :color="$sla['color']" icon="alarm">{{ $sla['label'] }}</x-admin.badge>@endif
    </x-slot:actions>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        {{-- گفتگو --}}
        <div class="min-w-0 space-y-5">
            <ol class="space-y-4">
                @foreach($messages as $m)
                    @php $isNote = $m->isNote(); $isStaffMsg = $m->isStaff(); @endphp
                    <li @if($loop->last) id="last" @endif class="flex gap-3 {{ $isStaffMsg || $isNote ? 'flex-row-reverse' : '' }}">
                        <span class="mt-1 grid size-9 shrink-0 place-items-center rounded-full text-sm font-extrabold {{ $isNote ? 'bg-amber-100 text-amber-700' : ($isStaffMsg ? 'bg-aron-600 text-white' : 'bg-slate-200 text-slate-700') }}">{{ mb_substr($m->user?->name ?? '؟', 0, 1) }}</span>
                        <div class="a-card min-w-0 max-w-[92%] p-4 {{ $isNote ? '!bg-amber-50 !border-amber-200' : ($isStaffMsg ? '!bg-aron-50/60' : '') }}">
                            <div class="mb-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                <strong class="text-ink">{{ $m->user?->name ?? 'حذف‌شده' }}</strong>
                                @if($isNote)<x-admin.badge color="amber" icon="eye-slash">یادداشت داخلی</x-admin.badge>@elseif($isStaffMsg)<x-admin.badge color="brand">پشتیبان</x-admin.badge>@else<x-admin.badge>مشتری</x-admin.badge>@endif
                                <time title="{{ Ticket::jdate($m->created_at) }}">{{ Ticket::ago($m->created_at) }}</time>
                            </div>
                            <div class="break-words text-sm leading-8 text-slate-700">{{ $m->html }}</div>
                            @if($m->attachments->isNotEmpty())
                                <ul class="mt-3 flex flex-wrap gap-2">
                                    @foreach($m->attachments as $a)
                                        <li><a href="{{ route('panel.tickets.attachment', [$ticket, $a]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-white px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:border-aron-300 hover:text-aron-700"><i class="ph ph-paperclip"></i><span class="max-w-[14rem] truncate">{{ $a->original_name }}</span><span class="font-normal text-slate-400">{{ $a->size_label }}</span></a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>

            <x-admin.card title="پاسخ" icon="paper-plane-tilt">
                <form method="POST" action="{{ route('panel.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4" id="reply-form">
                    @csrf
                    <div class="flex flex-wrap items-center gap-2" role="radiogroup">
                        <label class="cursor-pointer"><input type="radio" name="mode" value="reply" class="peer sr-only" @checked(old('mode', 'reply') === 'reply')><span class="inline-flex items-center gap-2 rounded-xl border border-line px-4 py-2 text-sm font-bold text-slate-500 peer-checked:border-aron-600 peer-checked:bg-aron-50 peer-checked:text-aron-700"><i class="ph ph-paper-plane-tilt"></i>پاسخ به مشتری</span></label>
                        <label class="cursor-pointer"><input type="radio" name="mode" value="note" class="peer sr-only" @checked(old('mode') === 'note')><span class="inline-flex items-center gap-2 rounded-xl border border-line px-4 py-2 text-sm font-bold text-slate-500 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700"><i class="ph ph-eye-slash"></i>یادداشت داخلی</span></label>
                        @if($canned->isNotEmpty())
                            <select id="canned-pick" class="a-select !w-auto ms-auto text-sm" aria-label="پاسخ آماده"><option value="">پاسخ آماده…</option>@foreach($canned as $c)<option value="{{ $c->id }}">{{ $c->title }}</option>@endforeach</select>
                        @endif
                    </div>
                    <x-admin.field name="body"><textarea name="body" id="reply-body" rows="7" class="a-textarea {{ $errors->has('body') ? 'has-error' : '' }}" required placeholder="متن پیام…">{{ old('body') }}</textarea></x-admin.field>
                    <x-admin.field name="attachments" :hint="'حداکثر '.$fa($rules['max_files']).' فایل تا '.$fa($rules['max_mb']).' مگابایت (jpg, png, webp, pdf, zip, txt)'">
                        <input type="file" name="attachments[]" multiple class="a-input" accept=".jpg,.jpeg,.png,.webp,.pdf,.zip,.txt">
                        @foreach($errors->get('attachments.*') as $msgs)<p class="mt-1.5 text-xs font-semibold text-coral-600">{{ $msgs[0] }}</p>@endforeach
                    </x-admin.field>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <x-admin.checkbox name="close_after" label="بعد از ارسال، تیکت بسته شود" :value="'1'" />
                        <x-admin.button type="submit" icon="paper-plane-tilt" loading>ارسال</x-admin.button>
                    </div>
                </form>
            </x-admin.card>
        </div>

        {{-- ستون کناری --}}
        <aside class="min-w-0 space-y-5">
            <x-admin.card title="مدیریت تیکت" icon="sliders-horizontal">
                <form method="POST" action="{{ route('panel.tickets.update', $ticket) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <x-admin.select name="status" label="وضعیت" :options="\App\Models\Ticket::STATUS_LABELS" :selected="$ticket->status" />
                    <x-admin.select name="priority" label="اولویت" :options="\App\Models\Ticket::PRIORITY_LABELS" :selected="$ticket->priority" />
                    <x-admin.select name="department_id" label="دپارتمان" :options="$departments->pluck('name', 'id')->all()" :selected="$ticket->department_id" placeholder="—" />
                    <x-admin.select name="assigned_to" label="مسئول" :options="$staff->pluck('name', 'id')->all()" :selected="$ticket->assigned_to" placeholder="بدون مسئول" />
                    <x-admin.button type="submit" icon="floppy-disk" class="w-full" loading>ذخیره تغییرات</x-admin.button>
                </form>
            </x-admin.card>

            <x-admin.card title="مشتری" icon="user">
                <div class="flex items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-2xl bg-aron-50 text-lg font-black text-aron-600">{{ mb_substr($ticket->user?->name ?? '؟', 0, 1) }}</span>
                    <div class="min-w-0"><div class="truncate font-extrabold text-ink">{{ $ticket->user?->name }}</div><div class="truncate text-xs text-slate-400" dir="ltr">{{ $ticket->user?->email }}</div></div>
                </div>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">عضویت</dt><dd class="font-bold">{{ Ticket::jdate($ticket->user?->created_at, 'Y/m/d') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">تعداد تیکت‌ها</dt><dd class="font-bold">{{ $fa($ticketCount) }}</dd></div>
                    @if(! is_null($ordersCount))<div class="flex justify-between"><dt class="text-slate-500">سفارش‌ها</dt><dd class="font-bold">{{ $fa($ordersCount) }}</dd></div>@endif
                    @if($ticket->order_code)<div class="flex justify-between"><dt class="text-slate-500">سفارش مرتبط</dt><dd class="font-bold" dir="ltr">{{ $ticket->order_code }}</dd></div>@endif
                    <div class="flex justify-between"><dt class="text-slate-500">دپارتمان</dt><dd class="font-bold">{{ $ticket->department?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">ثبت</dt><dd class="font-bold">{{ Ticket::jdate($ticket->created_at) }}</dd></div>
                    @if($ticket->rating)<div class="flex justify-between"><dt class="text-slate-500">رضایت</dt><dd class="font-bold text-amber-600"><i class="ph-fill ph-star"></i> {{ $fa($ticket->rating) }} از ۵</dd></div>@endif
                </dl>
                @if($ticket->rating_comment)<p class="mt-3 rounded-xl bg-slate-50 p-3 text-xs leading-6 text-slate-600">{{ $ticket->rating_comment }}</p>@endif
                @if($otherTickets->isNotEmpty())
                    <div class="mt-4 border-t border-line pt-3"><div class="mb-2 text-xs font-bold text-slate-500">تیکت‌های قبلی</div>
                        <ul class="space-y-1.5 text-sm">@foreach($otherTickets as $o)<li><a href="{{ route('panel.tickets.show', $o) }}" class="flex items-center justify-between gap-2 hover:text-aron-600"><span class="truncate">{{ $o->subject }}</span><x-admin.badge :color="$o->status === 'closed' ? 'slate' : 'sky'">{{ \App\Models\Ticket::STATUS_LABELS[$o->status] }}</x-admin.badge></a></li>@endforeach</ul></div>
                @endif
            </x-admin.card>

            <x-admin.card title="گزارش فعالیت" icon="clock-counter-clockwise">
                <ol class="space-y-3">
                    @foreach($events->reverse() as $e)
                        <li class="flex gap-3 text-sm">
                            <span class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-500"><i class="ph ph-{{ $e->icon }} text-sm"></i></span>
                            <div class="min-w-0"><div class="font-semibold text-slate-700">{{ $e->describe() }}</div><div class="text-xs text-slate-400">{{ $e->user?->name ?? 'سیستم' }} · {{ Ticket::ago($e->created_at) }}</div></div>
                        </li>
                    @endforeach
                </ol>
            </x-admin.card>

            <x-admin.confirm-delete :action="route('panel.tickets.destroy', $ticket)" label="حذف تیکت" message="این تیکت با همهٔ پیام‌ها و پیوست‌ها برای همیشه حذف شود؟" />
        </aside>
    </div>

    <x-slot:scripts>
        <script>
            (function () {
                var data = @json($canned->mapWithKeys(fn ($c) => [$c->id => $c->body]));
                var pick = document.getElementById('canned-pick'), body = document.getElementById('reply-body');
                if (pick) pick.addEventListener('change', function () {
                    if (!pick.value) return;
                    body.value = (body.value ? body.value.replace(/\s+$/, '') + '\n\n' : '') + data[pick.value];
                    pick.value = ''; body.focus();
                });
            })();
        </script>
    </x-slot:scripts>
</x-admin.layout>
