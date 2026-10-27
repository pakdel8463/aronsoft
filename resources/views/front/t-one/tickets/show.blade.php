@php use App\Models\Ticket; @endphp
@component('front.t-one.layouts.content')
    @include('front.t-one.tickets.partials.head', ['heading' => $ticket->subject, 'action' => ['url' => route('tickets.index'), 'label' => 'همه تیکت‌ها', 'icon' => 'arrow-right']])
    <div class="section !py-8 md:!py-10">
        <div class="container-x max-w-4xl">
            @if(session('success'))<div class="form-msg is-ok mb-4">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="form-msg is-err mb-4">{{ session('error') }}</div>@endif

            <div class="card mb-6 grid grid-cols-2 gap-4 p-4 text-sm sm:grid-cols-4 sm:p-5">
                <div><div class="text-xs text-slate-500">شمارهٔ تیکت</div><div class="mt-1 font-extrabold" dir="ltr">{{ $ticket->number }}</div></div>
                <div><div class="text-xs text-slate-500">وضعیت</div><div class="mt-1"><span class="chip {{ $ticket->status === 'waiting_customer' ? 'chip-blue' : '' }}">{{ $ticket->client_status_label }}</span></div></div>
                <div><div class="text-xs text-slate-500">دپارتمان</div><div class="mt-1 font-bold">{{ $ticket->department?->name ?? '—' }}</div></div>
                <div><div class="text-xs text-slate-500">ثبت‌شده</div><div class="mt-1 font-bold">{{ Ticket::jdate($ticket->created_at) }}</div></div>
                @if($ticket->order_code)<div class="col-span-2 sm:col-span-4 text-xs text-slate-500">سفارش مرتبط: <span class="font-bold text-slate-800" dir="ltr">{{ $ticket->order_code }}</span></div>@endif
            </div>

            <ol class="space-y-4" aria-label="گفتگو">
                @foreach($messages as $m)
                    @php $staff = $m->isStaff(); @endphp
                    <li @if($loop->last) id="last" @endif class="flex gap-3 {{ $staff ? '' : 'flex-row-reverse' }}">
                        <span class="mt-1 grid h-9 w-9 shrink-0 place-items-center rounded-full text-sm font-extrabold {{ $staff ? 'bg-tech-blue text-white' : 'bg-slate-200 text-slate-700' }}" aria-hidden="true">{{ $staff ? 'پ' : mb_substr($m->user?->name ?? '؟', 0, 1) }}</span>
                        <div class="min-w-0 max-w-[88%] rounded-2xl border p-4 {{ $staff ? 'border-brand-200 bg-brand-50/60 rounded-tr-sm' : 'border-slate-200 bg-white rounded-tl-sm' }}">
                            <div class="mb-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                <strong class="text-slate-800">{{ $staff ? 'پشتیبانی آرون‌سافت' : 'شما' }}</strong>
                                <time datetime="{{ $m->created_at->toIso8601String() }}" title="{{ Ticket::jdate($m->created_at) }}">{{ Ticket::ago($m->created_at) }}</time>
                            </div>
                            <div class="break-words text-sm leading-8 text-slate-800">{{ $m->html }}</div>
                            @if($m->attachments->isNotEmpty())
                                <ul class="mt-3 flex flex-wrap gap-2">
                                    @foreach($m->attachments as $a)
                                        <li><a href="{{ route('tickets.attachment', [$ticket, $a]) }}" class="chip chip-outline hover:border-tech-blue hover:text-tech-blue"><i class="ph ph-paperclip" aria-hidden="true"></i><span class="max-w-[14rem] truncate">{{ $a->original_name }}</span><span class="text-slate-400">{{ $a->size_label }}</span></a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>

            <div class="mt-8">
                @if($canReply)
                    <form method="POST" action="{{ route('tickets.reply', $ticket) }}" enctype="multipart/form-data" id="reply" class="card space-y-4 p-5 sm:p-6" data-ticket-form>
                        @csrf
                        <div>
                            <label class="form-label" for="r-body">پاسخ شما</label>
                            <textarea class="form-input" id="r-body" name="body" rows="5" maxlength="10000" required placeholder="پیام خود را بنویسید…">{{ old('body') }}</textarea>
                            @error('body')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        @include('front.t-one.tickets.partials.uploader')
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <button type="submit" form="close-form" class="btn btn-secondary" onclick="return confirm('تیکت بسته شود؟')"><i class="ph ph-check-circle" aria-hidden="true"></i> بستن تیکت</button>
                            <button type="submit" class="btn btn-primary" data-once><i class="ph ph-paper-plane-right" aria-hidden="true"></i> ارسال پاسخ</button>
                        </div>
                    </form>
                    <form id="close-form" method="POST" action="{{ route('tickets.close', $ticket) }}" class="hidden">@csrf</form>
                @elseif($ticket->isClosed())
                    <div class="card p-5 text-center sm:p-6">
                        <p class="font-bold text-slate-900"><i class="ph ph-lock-key text-slate-400" aria-hidden="true"></i> این تیکت بسته شده است.</p>
                        @if($canReopen)
                            <form method="POST" action="{{ route('tickets.reopen', $ticket) }}" class="mt-4">@csrf<button class="btn btn-soft"><i class="ph ph-arrows-clockwise" aria-hidden="true"></i> باز کردن دوباره</button></form>
                        @else
                            <p class="mt-2 text-sm text-slate-600">برای موضوع جدید، <a class="font-bold text-tech-blue" href="{{ route('tickets.create') }}">تیکت تازه‌ای ثبت کنید</a>.</p>
                        @endif
                    </div>
                @endif

                @if($canRate)
                    <form method="POST" action="{{ route('tickets.rate', $ticket) }}" class="card mt-5 space-y-3 p-5 sm:p-6">
                        @csrf
                        <h2 class="font-extrabold text-slate-900">از پشتیبانی چقدر راضی بودید؟</h2>
                        <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="امتیاز">
                            @foreach(range(1, 5) as $n)
                                <label class="cursor-pointer"><input type="radio" name="rating" value="{{ $n }}" class="peer sr-only" @checked(old('rating') == $n) required>
                                    <span class="flex h-11 min-w-[44px] items-center justify-center gap-1 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-600 transition peer-checked:border-tech-blue peer-checked:bg-tech-blue peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-tech-blue/40"><i class="ph ph-star" aria-hidden="true"></i>{{ \App\Helpers\Fa::toFa($n) }}</span></label>
                            @endforeach
                        </div>
                        @error('rating')<p class="form-error">{{ $message }}</p>@enderror
                        <textarea class="form-input" name="rating_comment" rows="2" maxlength="500" placeholder="توضیح (اختیاری)">{{ old('rating_comment') }}</textarea>
                        <button class="btn btn-primary">ثبت امتیاز</button>
                    </form>
                @elseif($ticket->rating)
                    <p class="mt-5 text-center text-sm text-slate-600">امتیاز شما: <strong class="text-slate-900">{{ \App\Helpers\Fa::toFa($ticket->rating) }} از ۵</strong> — ممنون از بازخوردتان.</p>
                @endif
            </div>
        </div>
    </div>
    @slot('bottomscript')
        <script>document.querySelectorAll('[data-ticket-form]').forEach(function (f) { f.addEventListener('submit', function () { var b = f.querySelector('[data-once]'); if (b) setTimeout(function () { b.disabled = true; }, 0); }); });</script>
    @endslot
@endcomponent
