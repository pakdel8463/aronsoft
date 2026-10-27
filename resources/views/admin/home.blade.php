@php
    $fa = fn ($v) => strtr((string) $v, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    $ago = fn ($d) => $d ? $fa($d->copy()->locale('fa')->diffForHumans()) : '';
    $R = fn ($name) => \Illuminate\Support\Facades\Route::has($name);

    // ---- chart geometry (inline SVG, no JS) ----
    $pts = $chart['points'];
    $W = 720; $H = 220; $padL = 8; $padR = 8; $padT = 16; $padB = 28;
    $n = count($pts);
    $stepX = ($W - $padL - $padR) / max(1, $n - 1);
    $yMax = $chart['max'] < 4 ? 4 : $chart['max'];
    $coords = [];
    foreach ($pts as $i => $p) {
        $coords[] = [$padL + $i * $stepX, $padT + ($H - $padT - $padB) * (1 - $p['count'] / $yMax), $p];
    }
    $line = collect($coords)->map(fn ($c, $i) => ($i ? 'L' : 'M') . round($c[0], 1) . ' ' . round($c[1], 1))->implode(' ');
    $area = $line . ' L' . round($padL + ($n - 1) * $stepX, 1) . ' ' . ($H - $padB) . ' L' . $padL . ' ' . ($H - $padB) . ' Z';
@endphp
<x-admin.layout title="داشبورد" :subtitle="'خوش آمدید ' . auth()->user()->name . '؛ خلاصه‌ی وضعیت سایت در یک نگاه'">
    <x-slot:actions>
        @if ($R('panel.posts.create'))
            <x-admin.button :href="route('panel.posts.create')" icon="plus">نوشته جدید</x-admin.button>
        @endif
    </x-slot:actions>

    {{-- stat cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-admin.stat-card label="نوشته‌ها" :value="$fa($stats['posts'])" icon="article" color="brand"
            :hint="$fa($stats['posts_published']) . ' منتشرشده'" :href="$R('panel.posts.index') ? route('panel.posts.index') : null" />
        <x-admin.stat-card label="نمونه‌کارها" :value="$fa($stats['portfolios'])" icon="briefcase" color="violet"
            :href="$R('panel.portfolios.index') ? route('panel.portfolios.index') : null" />
        <x-admin.stat-card label="لیدهای جدید" :value="$fa($stats['leads_new'])" icon="target" color="coral"
            :hint="'از مجموع ' . $fa($stats['leads_total'])" :href="$R('panel.leads.index') ? route('panel.leads.index') : null" />
        <x-admin.stat-card label="پیام‌های تماس جدید" :value="$fa($stats['messages_new'])" icon="envelope-simple" color="amber"
            :href="$R('panel.contact-messages.index') ? route('panel.contact-messages.index') : null" />
        <x-admin.stat-card label="دیدگاه در انتظار تأیید" :value="$fa($stats['comments_pending'])" icon="chat-centered-text" color="sky"
            :href="$R('panel.comments.index') ? route('panel.comments.index') : null" />
        <x-admin.stat-card label="مشترکین خبرنامه" :value="$fa($stats['newsletter'])" icon="megaphone" color="mint"
            :href="$R('panel.newsletter.index') ? route('panel.newsletter.index') : null" />
        @if (! is_null($stats['orders']))
            <x-admin.stat-card label="سفارش‌ها" :value="$fa($stats['orders'])" icon="shopping-bag" color="brand"
                :href="$R('panel.orders.index') ? route('panel.orders.index') : null" />
        @endif
        @if (! is_null($stats['tickets_open']))
            <x-admin.stat-card label="تیکت‌های باز" :value="$fa($stats['tickets_open'])" icon="lifebuoy" color="coral"
                :href="$R('panel.tickets.index') ? route('panel.tickets.index') : null" />
        @endif
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-3">
        {{-- leads chart --}}
        <x-admin.card class="xl:col-span-2" title="لیدهای ۳۰ روز اخیر" :subtitle="$fa($chart['total']) . ' لید جدید در این بازه'" icon="chart-line-up">
            <div class="-mx-1">
                <svg viewBox="0 0 {{ $W }} {{ $H }}" class="h-auto w-full" role="img" aria-label="نمودار لیدهای ۳۰ روز اخیر" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="leadFill" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0" stop-color="#2F4FF5" stop-opacity=".28" />
                            <stop offset="1" stop-color="#2F4FF5" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    @foreach ([0, .5, 1] as $g)
                        @php($gy = $padT + ($H - $padT - $padB) * $g)
                        <line x1="{{ $padL }}" x2="{{ $W - $padR }}" y1="{{ $gy }}" y2="{{ $gy }}" stroke="#e7eaf4" stroke-dasharray="4 6" />
                    @endforeach
                    <path d="{{ $area }}" fill="url(#leadFill)" />
                    <path d="{{ $line }}" fill="none" stroke="#2F4FF5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                    @foreach ($coords as [$cx, $cy, $p])
                        @if ($p['count'] > 0)
                            <circle cx="{{ round($cx, 1) }}" cy="{{ round($cy, 1) }}" r="4" fill="#fff" stroke="#2F4FF5" stroke-width="2.5" vector-effect="non-scaling-stroke"><title>{{ $p['label'] }} — {{ $fa($p['count']) }} لید</title></circle>
                        @endif
                    @endforeach
                </svg>
                <div class="mt-1 flex justify-between px-1 text-[11px] font-semibold text-slate-400" dir="ltr">
                    <span>{{ $pts[0]['label'] }}</span><span>{{ $pts[(int) ($n / 2)]['label'] }}</span><span>{{ $pts[$n - 1]['label'] }}</span>
                </div>
            </div>
        </x-admin.card>

        {{-- quick actions --}}
        <x-admin.card title="دسترسی سریع" icon="lightning">
            <div class="grid grid-cols-2 gap-2.5">
                @foreach ([
                    ['panel.posts.create', 'نوشته جدید', 'pencil-simple-line', 'brand'],
                    ['panel.portfolios.create', 'نمونه‌کار جدید', 'briefcase', 'violet'],
                    ['panel.landing.create', 'لندینگ جدید', 'browser', 'sky'],
                    ['panel.products.create', 'محصول جدید', 'package', 'mint'],
                    ['panel.proformas.create', 'پیش‌فاکتور', 'receipt', 'amber'],
                    ['panel.newsletter.compose', 'ارسال خبرنامه', 'paper-plane-tilt', 'coral'],
                ] as [$route, $label, $icon, $color])
                    @if ($R($route))
                        @php($c = ['brand' => 'bg-aron-50 text-aron-700 hover:bg-aron-100', 'violet' => 'bg-violet-50 text-violet-700 hover:bg-violet-100', 'sky' => 'bg-sky-50 text-sky-700 hover:bg-sky-100', 'mint' => 'bg-mint-50 text-mint-700 hover:bg-mint-100', 'amber' => 'bg-amber-50 text-amber-700 hover:bg-amber-100', 'coral' => 'bg-coral-50 text-coral-700 hover:bg-coral-100'][$color])
                        <a href="{{ route($route) }}" class="flex flex-col items-start gap-3 rounded-2xl p-4 text-sm font-extrabold transition {{ $c }}">
                            <i class="ph-fill ph-{{ $icon }} text-2xl"></i>{{ $label }}
                        </a>
                    @endif
                @endforeach
            </div>
        </x-admin.card>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-3">
        {{-- recent leads --}}
        <x-admin.card title="آخرین لیدها" icon="target" :padding="false">
            <x-slot:actions>@if ($R('panel.leads.index'))<a href="{{ route('panel.leads.index') }}" class="text-xs font-bold text-aron-600 hover:underline">همه</a>@endif</x-slot:actions>
            @forelse ($recentLeads as $lead)
                <a href="{{ $R('panel.leads.show') ? route('panel.leads.show', $lead) : '#' }}" class="flex items-center gap-3 border-t border-line/70 px-5 py-3.5 transition first:border-t-0 hover:bg-aron-50/40 sm:px-6">
                    <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-coral-50 text-sm font-extrabold text-coral-600">{{ \Illuminate\Support\Str::of($lead->full_name ?: '؟')->substr(0, 1) }}</span>
                    <span class="min-w-0 flex-1">
                        <strong class="block truncate text-sm font-bold text-ink">{{ $lead->full_name ?: 'بدون نام' }}</strong>
                        <small class="block truncate text-xs text-slate-400">{{ $lead->request_type ?: $lead->mobile ?: $lead->email }}</small>
                    </span>
                    <span class="flex flex-col items-end gap-1">
                        @unless ($lead->is_contacted)<x-admin.badge color="coral" dot>جدید</x-admin.badge>@endunless
                        <small class="text-[11px] text-slate-400">{{ $ago($lead->created_at) }}</small>
                    </span>
                </a>
            @empty
                <x-admin.empty-state icon="target" title="هنوز لیدی ثبت نشده" description="با ثبت فرم‌های سایت، لیدها اینجا نمایش داده می‌شوند." class="py-10" />
            @endforelse
        </x-admin.card>

        {{-- recent messages --}}
        <x-admin.card title="آخرین پیام‌های تماس" icon="envelope-simple" :padding="false">
            <x-slot:actions>@if ($R('panel.contact-messages.index'))<a href="{{ route('panel.contact-messages.index') }}" class="text-xs font-bold text-aron-600 hover:underline">همه</a>@endif</x-slot:actions>
            @forelse ($recentMessages as $m)
                <a href="{{ $R('panel.contact-messages.show') ? route('panel.contact-messages.show', $m) : '#' }}" class="flex items-center gap-3 border-t border-line/70 px-5 py-3.5 transition first:border-t-0 hover:bg-aron-50/40 sm:px-6">
                    <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-amber-50 text-amber-600"><i class="ph-fill ph-envelope-simple text-lg"></i></span>
                    <span class="min-w-0 flex-1">
                        <strong class="block truncate text-sm font-bold text-ink">{{ $m->subject ?: $m->name }}</strong>
                        <small class="block truncate text-xs text-slate-400">{{ $m->name }} · {{ \Illuminate\Support\Str::limit(strip_tags($m->message), 36) }}</small>
                    </span>
                    <span class="flex flex-col items-end gap-1">
                        @if ($m->status === 'new')<x-admin.badge color="amber" dot>جدید</x-admin.badge>@endif
                        <small class="text-[11px] text-slate-400">{{ $ago($m->created_at) }}</small>
                    </span>
                </a>
            @empty
                <x-admin.empty-state icon="envelope-simple-open" title="پیامی وجود ندارد" description="پیام‌های فرم تماس اینجا دیده می‌شوند." class="py-10" />
            @endforelse
        </x-admin.card>

        {{-- recent comments --}}
        <x-admin.card title="آخرین دیدگاه‌ها" icon="chat-centered-text" :padding="false">
            <x-slot:actions>@if ($R('panel.comments.index'))<a href="{{ route('panel.comments.index') }}" class="text-xs font-bold text-aron-600 hover:underline">همه</a>@endif</x-slot:actions>
            @forelse ($recentComments as $c)
                <a href="{{ $R('panel.comments.show') ? route('panel.comments.show', $c) : '#' }}" class="flex items-center gap-3 border-t border-line/70 px-5 py-3.5 transition first:border-t-0 hover:bg-aron-50/40 sm:px-6">
                    <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-sky-50 text-sm font-extrabold text-sky-600">{{ \Illuminate\Support\Str::of($c->name ?: '؟')->substr(0, 1) }}</span>
                    <span class="min-w-0 flex-1">
                        <strong class="block truncate text-sm font-bold text-ink">{{ $c->name ?: 'ناشناس' }}</strong>
                        <small class="block truncate text-xs text-slate-400">{{ \Illuminate\Support\Str::limit(strip_tags($c->body), 44) }}</small>
                    </span>
                    <span class="flex flex-col items-end gap-1">
                        @unless ($c->is_approved)<x-admin.badge color="sky" dot>در انتظار</x-admin.badge>@endunless
                        <small class="text-[11px] text-slate-400">{{ $ago($c->created_at) }}</small>
                    </span>
                </a>
            @empty
                <x-admin.empty-state icon="chat-centered-dots" title="دیدگاهی ثبت نشده" description="دیدگاه‌های نوشته‌ها اینجا نمایش داده می‌شوند." class="py-10" />
            @endforelse
        </x-admin.card>
    </div>
</x-admin.layout>
