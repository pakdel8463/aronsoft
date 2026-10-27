@php
    /** @var array $L  داده‌ی لندینگ (به فایل‌های webdev/seo/... نگاه کنید) */
    use App\View\Seo;
    $slugUrl = url()->current();
    $orderUrl = url('/order'.(!empty($L['order_type']) ? '?type='.$L['order_type'] : ''));
    $L['cta_primary'] = $L['cta_primary'] ?? ['ثبت سفارش پروژه', $orderUrl];
    $L['cta_secondary'] = $L['cta_secondary'] ?? ['درخواست مشاوره رایگان', '#lead'];

    Seo::meta([
        'title' => $L['seo_title'],
        'description' => $L['seo_description'],
        'canonical' => $slugUrl,
    ]);

    $graph = [
        Seo::breadcrumbs([['خانه', url('/')], [$L['name'], $slugUrl]]),
        Seo::service($L['name'], $L['seo_description'], $slugUrl, $L['low_price'] ?? null),
    ];
    if (!empty($L['faqs'])) { $graph[] = Seo::faq($L['faqs']); }
    $hasPortfolios = isset($portfolios) && $portfolios->count();
@endphp

@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd($graph) !!}
    @endslot

    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden" aria-labelledby="lp-title">
        <div class="hero-glow pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 text-slate-300/60" aria-hidden="true"></div>
        <div class="container-x relative grid items-center gap-10 py-12 md:py-16 lg:grid-cols-12 lg:gap-8 lg:py-20">
            <div class="min-w-0 {{ !empty($L['visual']) ? 'lg:col-span-6' : 'lg:col-span-12 mx-auto max-w-3xl text-center' }}">
                @include('front.t-one.partials.breadcrumb', ['items' => [['خانه', url('/')], [$L['name'], null]]])
                <span class="badge mb-4"><i class="ph {{ $L['badge_icon'] ?? 'ph-sparkle' }}" aria-hidden="true"></i> {{ $L['eyebrow'] }}</span>
                <h1 id="lp-title" class="h1-page">{{ $L['h1'][0] }} <span class="text-tech-blue">{{ $L['h1'][1] }}</span>@if(!empty($L['h1'][2])) {{ $L['h1'][2] }}@endif</h1>
                <p class="lead mt-4 {{ empty($L['visual']) ? 'mx-auto max-w-2xl' : 'max-w-xl' }}">{{ $L['lead'] }}</p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row {{ empty($L['visual']) ? 'sm:justify-center' : '' }}">
                    <a href="{{ $L['cta_primary'][1] }}" class="btn btn-primary btn-lg">{{ $L['cta_primary'][0] }} <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                    <a href="{{ $L['cta_secondary'][1] }}" class="btn btn-secondary btn-lg">{{ $L['cta_secondary'][0] }}</a>
                </div>
                @if(!empty($L['bullets']))
                    <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-600 {{ empty($L['visual']) ? 'justify-center' : '' }}">
                        @foreach($L['bullets'] as $b)
                            <li class="flex items-center gap-1.5"><i class="ph-fill ph-check-circle text-lg text-tech-blue" aria-hidden="true"></i> {{ $b }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @if(!empty($L['visual']))
                <div class="relative min-w-0 lg:col-span-6" aria-hidden="true">
                    @include('front.t-one.landings._parts.visuals', ['v' => $L['visual']])
                </div>
            @endif
        </div>
    </section>

    {{-- ===== TECH ===== --}}
    @if(!empty($L['tech']))
        <section class="border-y border-slate-200 bg-slate-50" aria-label="ابزارها و تکنولوژی‌ها">
            <div class="container-x py-6">
                @if(!empty($L['tech_title']))<p class="mb-3 text-center text-xs font-bold text-slate-500">{{ $L['tech_title'] }}</p>@endif
                <ul class="flex flex-wrap items-center justify-center gap-2.5" dir="ltr">
                    @foreach($L['tech'] as [$ic, $name])
                        <li class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-bold text-slate-700"><i class="ph {{ $ic }} text-lg text-tech-blue" aria-hidden="true"></i>{{ $name }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ===== PROBLEM / SOLUTION ===== --}}
    @if(!empty($L['problem']))
        <section class="section" aria-labelledby="lp-problem">
            <div class="container-x">
                <div class="section-head reveal"><h2 id="lp-problem" class="h2-section">{{ $L['problem']['title'] }}</h2>@if(!empty($L['problem']['sub']))<p class="lead mt-3">{{ $L['problem']['sub'] }}</p>@endif</div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="card reveal p-6">
                        <h3 class="mb-3 flex items-center gap-2 text-base font-bold text-slate-900"><span class="icon-tile-sm !bg-red-50 !text-red-600"><i class="ph ph-warning" aria-hidden="true"></i></span> چالش‌های رایج</h3>
                        <ul class="space-y-2">@foreach($L['problem']['problems'] as $x)<li class="flex gap-2 text-sm leading-7 text-slate-600"><i class="ph ph-x mt-1.5 text-red-500" aria-hidden="true"></i>{{ $x }}</li>@endforeach</ul>
                    </div>
                    <div class="card reveal border-brand-200 bg-brand-50/40 p-6">
                        <h3 class="mb-3 flex items-center gap-2 text-base font-bold text-slate-900"><span class="icon-tile-sm"><i class="ph ph-seal-check" aria-hidden="true"></i></span> راه‌حل ما</h3>
                        <ul class="space-y-2">@foreach($L['problem']['solutions'] as $x)<li class="flex gap-2 text-sm leading-7 text-slate-700"><i class="ph-bold ph-check mt-1.5 text-tech-blue" aria-hidden="true"></i>{{ $x }}</li>@endforeach</ul>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ===== FEATURES ===== --}}
    @if(!empty($L['features']))
        <section id="services" class="section {{ !empty($L['problem']) ? 'section-alt' : '' }}" aria-labelledby="lp-features">
            <div class="container-x">
                <div class="section-head reveal"><h2 id="lp-features" class="h2-section">{{ $L['features']['title'] }}</h2>@if(!empty($L['features']['sub']))<p class="lead mt-3">{{ $L['features']['sub'] }}</p>@endif</div>
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($L['features']['items'] as [$ic, $t, $d])
                        <li class="card card-hover reveal p-6"><span class="icon-tile mb-4"><i class="ph {{ $ic }}" aria-hidden="true"></i></span><h3 class="text-[1.0625rem] font-bold text-slate-900">{{ $t }}</h3><p class="mt-2 text-sm leading-7 text-slate-600">{{ $d }}</p></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ===== HIGHLIGHT (بلوک اختصاصی هر صفحه) ===== --}}
    @if(!empty($L['highlight']))
        @php $H = $L['highlight']; @endphp
        <section id="{{ $H['id'] ?? 'highlight' }}" class="section {{ empty($L['problem']) ? '' : '' }}" aria-labelledby="lp-highlight">
            <div class="container-x">
                <div class="grid items-center gap-8 rounded-3xl bg-slate-900 p-6 text-white sm:p-10 lg:grid-cols-2 lg:gap-12 lg:p-14">
                    <div>
                        <span class="badge !border-white/20 !bg-white/10 !text-blue-200 mb-4"><i class="ph {{ $H['icon'] ?? 'ph-star' }}" aria-hidden="true"></i> {{ $H['eyebrow'] }}</span>
                        <h2 id="lp-highlight" class="h2-section !text-white">{{ $H['title'][0] }} <span class="text-blue-300" dir="auto">{{ $H['title'][1] }}</span>@if(!empty($H['title'][2])) {{ $H['title'][2] }}@endif</h2>
                        <p class="mt-3 leading-8 text-slate-300">{{ $H['text'] }}</p>
                        <ul class="mt-5 space-y-2.5">@foreach($H['bullets'] as $b)<li class="flex items-start gap-2.5 text-sm leading-7 text-slate-200"><i class="ph-bold ph-check mt-1.5 text-blue-300" aria-hidden="true"></i>{{ $b }}</li>@endforeach</ul>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($H['tiles'] as [$ic, $t, $d])
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5"><span class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 text-xl text-blue-200"><i class="ph {{ $ic }}" aria-hidden="true"></i></span><h3 class="text-sm font-bold text-white" dir="auto">{{ $t }}</h3><p class="mt-1 text-xs leading-6 text-slate-400">{{ $d }}</p></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ===== TIMELINE / DEPARTMENTS (درباره ما) ===== --}}
    @if(!empty($L['timeline']))
        <section id="history" class="section section-alt" aria-labelledby="lp-history">
            <div class="container-x grid gap-10 lg:grid-cols-12">
                <div class="reveal lg:col-span-5">
                    <span class="eyebrow">{{ $L['timeline']['eyebrow'] }}</span>
                    <h2 id="lp-history" class="h2-section">{{ $L['timeline']['title'] }}</h2>
                    <p class="lead mt-3">{{ $L['timeline']['text'] }}</p>
                </div>
                <ol class="relative space-y-4 lg:col-span-7">
                    @foreach($L['timeline']['items'] as [$year, $t, $d])
                        <li class="card reveal flex gap-4 p-5"><span class="num flex h-11 w-16 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-sm font-extrabold text-tech-blue">{{ $year }}</span><div><h3 class="font-bold text-slate-900">{{ $t }}</h3><p class="mt-1 text-sm leading-7 text-slate-600">{{ $d }}</p></div></li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif
    @if(!empty($L['departments']))
        <section id="departments" class="section" aria-labelledby="lp-depts">
            <div class="container-x">
                <div class="section-head reveal"><h2 id="lp-depts" class="h2-section">{{ $L['departments']['title'] }}</h2><p class="lead mt-3">{{ $L['departments']['sub'] }}</p></div>
                <ul class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                    @foreach($L['departments']['items'] as [$ic, $t, $d])
                        <li class="card reveal p-5 text-center"><span class="icon-tile mx-auto mb-3"><i class="ph {{ $ic }}" aria-hidden="true"></i></span><h3 class="text-sm font-bold text-slate-900" dir="auto">{{ $t }}</h3><p class="mt-0.5 text-xs text-slate-500" dir="auto">{{ $d }}</p></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ===== PROCESS ===== --}}
    @if(!empty($L['steps']))
        <section id="process" class="section section-alt" aria-labelledby="lp-steps">
            <div class="container-x">
                <div class="section-head reveal"><h2 id="lp-steps" class="h2-section">{{ $L['steps']['title'] }}</h2>@if(!empty($L['steps']['sub']))<p class="lead mt-3">{{ $L['steps']['sub'] }}</p>@endif</div>
                <ol class="grid gap-5 sm:grid-cols-2 lg:grid-cols-{{ min(count($L['steps']['items']), 4) }}">
                    @foreach($L['steps']['items'] as $i => [$t, $d])
                        <li class="card reveal p-6"><span class="step-num mb-4 num">{{ \App\Helpers\Fa::toFa($i + 1) }}</span><h3 class="font-bold text-slate-900">{{ $t }}</h3><p class="mt-2 text-sm leading-7 text-slate-600">{{ $d }}</p></li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- ===== DELIVERABLES ===== --}}
    @if(!empty($L['deliverables']))
        <section class="section" aria-labelledby="lp-deliver">
            <div class="container-x grid items-start gap-8 lg:grid-cols-12">
                <div class="reveal lg:col-span-5"><h2 id="lp-deliver" class="h2-section">{{ $L['deliverables']['title'] }}</h2>@if(!empty($L['deliverables']['sub']))<p class="lead mt-3">{{ $L['deliverables']['sub'] }}</p>@endif</div>
                <ul class="grid gap-3 sm:grid-cols-2 lg:col-span-7">
                    @foreach($L['deliverables']['items'] as $x)
                        <li class="card reveal flex items-start gap-3 p-4"><i class="ph-fill ph-check-circle mt-0.5 text-xl text-tech-blue" aria-hidden="true"></i><span class="text-sm font-semibold leading-7 text-slate-700">{{ $x }}</span></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ===== PACKAGES ===== --}}
    @if(!empty($L['packages']))
        <section id="pricing" class="section section-alt" aria-labelledby="lp-pricing">
            <div class="container-x">
                <div class="section-head reveal"><h2 id="lp-pricing" class="h2-section">{{ $L['packages']['title'] }}</h2>@if(!empty($L['packages']['sub']))<p class="lead mt-3">{{ $L['packages']['sub'] }}</p>@endif</div>
                <ul class="grid items-stretch gap-5 md:grid-cols-3">
                    @foreach($L['packages']['items'] as $pk)
                        <li class="reveal">
                            <div class="card relative flex h-full flex-col p-6 {{ !empty($pk['featured']) ? '!border-tech-blue shadow-card ring-1 ring-tech-blue/30' : '' }}">
                                @if(!empty($pk['featured']))<span class="badge absolute -top-3 start-6 !bg-tech-blue !text-white">{{ $pk['badge'] ?? 'پیشنهادی' }}</span>@endif
                                <h3 class="text-lg font-extrabold text-slate-900">{{ $pk['name'] }}</h3>
                                @if(!empty($pk["desc"]))<p class="mt-1 text-sm leading-6 text-slate-600">{{ $pk["desc"] }}</p>@endif
                                <div class="my-4 border-y border-slate-100 py-4">
                                    @if(!empty($pk['price_note']))<span class="block text-xs text-slate-500">{{ $pk['price_note'] }}</span>@endif
                                    <span class="num text-2xl font-extrabold text-slate-900">{{ $pk['price'] }}</span>
                                    @if(!empty($pk['per']))<span class="text-xs text-slate-500"> {{ $pk['per'] }}</span>@endif
                                </div>
                                <ul class="check-list mb-6 flex-1 space-y-1">@foreach($pk['features'] as $f)<li>{{ $f }}</li>@endforeach</ul>
                                <a href="{{ $pk['href'] ?? $orderUrl }}" class="btn btn-block {{ !empty($pk['featured']) ? 'btn-primary' : 'btn-secondary' }}">{{ $pk['cta'] ?? 'استعلام قیمت' }}</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if(!empty($L['packages']['note']))<p class="mt-5 text-center text-xs text-slate-500">{{ $L['packages']['note'] }}</p>@endif
            </div>
        </section>
    @endif

    {{-- ===== PORTFOLIOS ===== --}}
    @if($hasPortfolios)
        <section id="portfolio" class="section" aria-labelledby="lp-portfolio">
            <div class="container-x">
                <div class="mb-8 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-end">
                    <div><h2 id="lp-portfolio" class="h2-section">{{ $L['portfolio_title'] ?? 'نمونه‌کارهای مرتبط' }}</h2></div>
                    <a href="{{ route('portfolio.index') }}" class="link-arrow">مشاهده همه <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($portfolios as $p)<div class="reveal">@include('front.t-one.partials.portfolio-card', ['p' => $p])</div>@endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===== COURSES (آکادمی) ===== --}}
    @if(!empty($L['courses']))
        <section id="courses" class="section {{ $hasPortfolios ? 'section-alt' : '' }}" aria-labelledby="lp-courses">
            <div class="container-x">
                <div class="section-head reveal"><h2 id="lp-courses" class="h2-section">{{ $L['courses']['title'] }}</h2><p class="lead mt-3">{{ $L['courses']['sub'] }}</p></div>
                <ul class="grid gap-5 md:grid-cols-3">
                    @foreach($L['courses']['items'] as [$ic, $t, $d, $meta, $status])
                        <li class="card reveal flex flex-col p-6">
                            <div class="mb-4 flex items-center justify-between"><span class="icon-tile"><i class="ph {{ $ic }}" aria-hidden="true"></i></span><span class="chip {{ $status === 'تکمیل‌شده' ? 'bg-emerald-50 text-emerald-700' : 'chip-blue' }}">{{ $status }}</span></div>
                            <h3 class="font-bold text-slate-900">{{ $t }}</h3><p class="mt-2 flex-1 text-sm leading-7 text-slate-600">{{ $d }}</p>
                            <p class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-slate-500"><i class="ph ph-clock" aria-hidden="true"></i>{{ $meta }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ===== FAQ ===== --}}
    @if(!empty($L['faqs']))
        <section class="section {{ ($hasPortfolios || !empty($L['courses'])) ? '' : 'section-alt' }}" aria-labelledby="lp-faq">
            <div class="container-x max-w-3xl">
                <div class="section-head reveal"><h2 id="lp-faq" class="h2-section">سؤالات متداول</h2></div>
                <div class="faq reveal space-y-3">
                    @foreach($L['faqs'] as [$q, $a])
                        <details><summary><span>{{ $q }}</span><i class="ph ph-caret-down" aria-hidden="true"></i></summary><p>{{ $a }}</p></details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===== LEAD + FINAL CTA ===== --}}
    <section id="lead" class="section" aria-labelledby="lp-lead">
        <div class="container-x">
            <div class="grid items-center gap-8 overflow-hidden rounded-3xl bg-slate-900 p-6 sm:p-10 lg:grid-cols-2 lg:gap-12 lg:p-14">
                <div class="text-center lg:text-start">
                    <h2 id="lp-lead" class="h2-section !text-white">{{ $L['final']['title'] ?? 'آماده شروع هستید؟' }}</h2>
                    <p class="mt-3 leading-8 text-slate-300">{{ $L['final']['text'] ?? 'پروژه‌تان را ثبت کنید یا شماره‌تان را بگذارید تا کارشناس ما تماس بگیرد.' }}</p>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center lg:justify-start">
                        <a href="{{ $orderUrl }}" class="btn btn-lg bg-white text-slate-900 hover:bg-slate-100">ثبت سفارش پروژه <i class="ph ph-arrow-left" aria-hidden="true"></i></a>
                        <a href="{{ url('/contact') }}" class="btn btn-lg border border-white/30 text-white hover:bg-white/10">تماس با ما</a>
                    </div>
                </div>
                <div class="rounded-2xl bg-white p-5 sm:p-6">
                    <h3 class="mb-1 text-base font-bold text-slate-900">{{ $L['form']['title'] ?? 'درخواست مشاوره رایگان' }}</h3>
                    <p class="mb-4 text-sm text-slate-600">{{ $L['form']['text'] ?? 'شماره موبایل را وارد کنید؛ در اولین فرصت تماس می‌گیریم.' }}</p>
                    @include('front.t-one.partials.lead-form', ['page' => $L['slug'], 'type' => $L['form']['type'] ?? $L['name'], 'cta' => 'درخواست تماس', 'withName' => true, 'withMessage' => $L['form']['message'] ?? false])
                </div>
            </div>
        </div>
    </section>

    {{-- نوار CTA چسبان --}}
    <div class="sticky-cta" data-sticky-cta role="region" aria-label="دسترسی سریع">
        <span class="hidden ps-2 text-sm font-bold text-slate-700 sm:block" dir="auto">{{ $L['name'] }}</span>
        <div class="flex flex-1 gap-2 sm:flex-none">
            <a href="#lead" class="btn btn-secondary btn-sm flex-1 sm:flex-none">مشاوره رایگان</a>
            <a href="{{ $orderUrl }}" class="btn btn-primary btn-sm flex-1 sm:flex-none">ثبت سفارش</a>
        </div>
    </div>
@endcomponent
