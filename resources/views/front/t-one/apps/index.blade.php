@php
    use App\View\Seo;
    Seo::meta([
        'title' => 'محصولات آرون سافت؛ نرم‌افزارها و اپلیکیشن‌های ما',
        'description' => 'محصولات اختصاصی تیم آرون سافت: نرم‌افزارها و اپلیکیشن‌هایی که خودمان طراحی، توسعه و پشتیبانی می‌کنیم.',
        'canonical' => route('product-pages.index'),
    ]);
    $crumbs = [['خانه', url('/')], ['محصولات', null]];
    $list = [
        '@type' => 'ItemList',
        'itemListElement' => $pages->values()->map(fn ($p, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => $p->publicUrl(), 'name' => $p->name])->all(),
    ];
@endphp
@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd($pages->isNotEmpty() ? [Seo::breadcrumbs([['خانه', url('/')], ['محصولات', route('product-pages.index')]]), $list] : Seo::breadcrumbs([['خانه', url('/')], ['محصولات', route('product-pages.index')]])) !!}
    @endslot
    @include('front.t-one.partials.page-header', ['crumbs' => $crumbs, 'title' => 'محصولات آرون سافت', 'lead' => 'علاوه بر پروژه‌های سفارشی، نرم‌افزارها و اپلیکیشن‌هایی هم می‌سازیم که خودمان توسعه و پشتیبانی می‌کنیم.'])

    <section class="section" aria-label="فهرست محصولات">
        <div class="container-x">
            @if($pages->isNotEmpty())
                <ul class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($pages as $p)
                        @php
                            $style = '--pc:'.$p->accentColor().';--pc2:'.$p->accent2Color().';--pc-on:'.$p->onAccent().';';
                            $hero = $p->section('hero');
                            $logo = $p->logoUrl(); $card = $p->cardImageUrl();
                        @endphp
                        <li class="reveal">
                            <a href="{{ $p->publicUrl() }}" class="pp pp-card pp-card-hover group flex h-full flex-col overflow-hidden" style="{{ $style }}">
                                <div class="relative flex h-44 items-center justify-center overflow-hidden" style="background-image: linear-gradient(135deg, var(--pc), var(--pc2));">
                                    @if($card)
                                        <img src="{{ $card }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                                    @else
                                        <div class="absolute inset-0 opacity-30 bg-dot-pattern text-white" aria-hidden="true"></div>
                                        <span class="relative flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl bg-white/95 text-4xl shadow-xl" style="color: var(--pc)">
                                            @if($logo)<img src="{{ $logo }}" alt="" class="h-full w-full object-contain p-3">@else<i class="ph {{ $p->icon ?: 'ph-sparkle' }}" aria-hidden="true"></i>@endif
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-1 flex-col p-6">
                                    @if($p->category_label)<span class="pp-badge mb-3 self-start">{{ $p->category_label }}</span>@endif
                                    <h2 class="text-lg font-extrabold text-slate-900">{{ $p->name }}@if($p->name_en) <span class="text-xs font-semibold text-slate-400" dir="ltr">{{ $p->name_en }}</span>@endif</h2>
                                    <p class="mt-2 flex-1 text-sm leading-8 text-slate-600">{{ $p->tagline ?: $hero['subtitle'] }}</p>
                                    <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-bold" style="color: var(--pc-deep)">مشاهده‌ی محصول <i class="ph ph-arrow-left transition-transform group-hover:-translate-x-1" aria-hidden="true"></i></span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="card mx-auto max-w-lg p-10 text-center">
                    <span class="icon-tile mx-auto mb-4"><i class="ph ph-package" aria-hidden="true"></i></span>
                    <h2 class="text-lg font-bold text-slate-900">به‌زودی</h2>
                    <p class="mt-2 text-sm text-slate-600">محصولات ما به‌زودی در این صفحه معرفی می‌شوند.</p>
                    <a href="{{ route('portfolio.index') }}" class="btn btn-secondary mt-5">مشاهده نمونه‌کارها</a>
                </div>
            @endif
        </div>
    </section>
@endcomponent
