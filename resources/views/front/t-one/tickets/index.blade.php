@component('front.t-one.layouts.content')
    @include('front.t-one.tickets.partials.head', ['heading' => 'تیکت‌های من', 'sub' => 'درخواست‌های پشتیبانی خود را ثبت و پیگیری کنید.', 'action' => ['url' => route('tickets.create'), 'label' => 'تیکت جدید']])
    <div class="section !py-8 md:!py-10">
        <div class="container-x max-w-4xl">
            <form method="GET" action="{{ route('tickets.index') }}" class="mb-6 flex flex-wrap items-center gap-2">
                <a href="{{ route('tickets.index', array_filter(['department' => $department])) }}" class="chip-filter {{ $status ? '' : 'is-active' }}">همه</a>
                @foreach($filters as $key => $label)
                    <a href="{{ route('tickets.index', array_filter(['status' => $key, 'department' => $department])) }}" class="chip-filter {{ $status === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
                <span class="flex-1"></span>
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <label class="sr-only" for="dept">دپارتمان</label>
                <select id="dept" name="department" class="form-input select-rtl-fix !w-auto min-w-[10rem]" onchange="this.form.submit()">
                    <option value="">همه دپارتمان‌ها</option>
                    @foreach($departments as $d)<option value="{{ $d->id }}" @selected($department === $d->id)>{{ $d->name }}</option>@endforeach
                </select>
                <noscript><button class="btn btn-secondary">اعمال</button></noscript>
            </form>

            @if($unread > 0)
                <div class="form-msg is-ok mb-4 flex items-center gap-2"><i class="ph ph-bell" aria-hidden="true"></i> {{ \App\Helpers\Fa::toFa($unread) }} تیکت دارای پاسخ جدید پشتیبانی است.</div>
            @endif

            @forelse($tickets as $t)
                <a href="{{ route('tickets.show', $t) }}" class="card card-hover mb-3 flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:p-5 {{ $t->client_unread ? 'border-tech-blue/50 bg-brand-50/40' : '' }}">
                    <span class="icon-tile-sm hidden sm:flex"><i class="ph ph-{{ $t->isClosed() ? 'check-circle' : 'chats-circle' }}" aria-hidden="true"></i></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-2">
                            @if($t->client_unread)<span class="h-2.5 w-2.5 shrink-0 rounded-full bg-tech-blue" title="پاسخ جدید"></span>@endif
                            <span class="truncate font-bold text-slate-900">{{ $t->subject }}</span>
                        </span>
                        <span class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                            <span dir="ltr" class="font-semibold">{{ $t->number }}</span>
                            @if($t->department)<span class="chip">{{ $t->department->name }}</span>@endif
                            <span>{{ \App\Models\Ticket::ago($t->last_reply_at ?? $t->created_at) }}</span>
                        </span>
                    </span>
                    <span class="flex items-center gap-2">
                        @if($t->client_unread)<span class="chip chip-blue">پاسخ جدید</span>@endif
                        <span class="chip {{ $t->status === 'waiting_customer' ? 'chip-blue' : '' }} {{ $t->isClosed() ? 'chip-outline' : '' }}">{{ $t->client_status_label }}</span>
                    </span>
                </a>
            @empty
                <div class="card p-10 text-center">
                    <span class="icon-tile mx-auto mb-4"><i class="ph ph-chats-circle" aria-hidden="true"></i></span>
                    <h2 class="font-extrabold text-slate-900">{{ $total ? 'تیکتی با این فیلتر پیدا نشد' : 'هنوز تیکتی ثبت نکرده‌اید' }}</h2>
                    <p class="mt-2 text-sm text-slate-600">برای هر سؤال یا مشکلی یک تیکت ثبت کنید تا کارشناسان ما پاسخ دهند.</p>
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary mt-5">ثبت اولین تیکت</a>
                </div>
            @endforelse

            @if($tickets->hasPages())<div class="mt-6">{{ $tickets->links('vendor.pagination.tech') }}</div>@endif
        </div>
    </div>
@endcomponent
