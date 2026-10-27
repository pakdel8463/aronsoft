@php
    use App\View\Seo;
    $title = $currentCategory ? 'نمونه‌کارهای '.$currentCategory->name : 'نمونه‌کارها';
    $pageUrl = $currentCategory ? route('portfolio.category', $currentCategory->slug) : route('portfolio.index');
    $crumbs = [['خانه', url('/')], ['نمونه‌کارها', $currentCategory ? route('portfolio.index') : null]];
    if ($currentCategory) { $crumbs[] = [$currentCategory->name, null]; }
@endphp
@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd(Seo::breadcrumbs(collect($crumbs)->map(fn ($c, $i) => [$c[0], $c[1] ?: $pageUrl])->all())) !!}
    @endslot

    @include('front.t-one.partials.page-header', [
        'crumbs' => $crumbs,
        'title' => $currentCategory ? 'پروژه‌های '.$currentCategory->name : 'پروژه‌های منتخب ما',
        'lead' => $currentCategory?->description ?: 'ما فقط کد نمی‌نویسیم؛ اکوسیستم‌های دیجیتال مهندسی می‌کنیم. نگاهی به آخرین پروژه‌های ما بیندازید.',
    ])

    <section class="section" aria-label="فهرست نمونه‌کارها">
        <div class="container-x">
            <nav class="mb-8 flex flex-wrap gap-2" aria-label="دسته‌بندی نمونه‌کارها">
                <a href="{{ route('portfolio.index') }}" @if(!$currentCategory) aria-current="page" @endif class="chip-filter {{ !$currentCategory ? 'is-active' : '' }}">همه پروژه‌ها</a>
                @foreach($categories as $cat)
                    <a href="{{ route('portfolio.category', $cat->slug) }}" @if($currentCategory && $currentCategory->id === $cat->id) aria-current="page" @endif class="chip-filter {{ ($currentCategory && $currentCategory->id === $cat->id) ? 'is-active' : '' }}">{{ $cat->name }}</a>
                @endforeach
            </nav>

            @if($portfolios->count())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($portfolios as $p)
                        @include('front.t-one.partials.portfolio-card', ['p' => $p])
                    @endforeach
                </div>
                <div class="mt-10">{{ $portfolios->links() }}</div>
            @else
                <div class="card mx-auto max-w-lg p-10 text-center">
                    <span class="icon-tile mx-auto mb-4"><i class="ph ph-folder-open" aria-hidden="true"></i></span>
                    <h2 class="text-lg font-bold text-slate-900">هنوز پروژه‌ای در این بخش منتشر نشده</h2>
                    <p class="mt-2 text-sm text-slate-600">به‌زودی نمونه‌کارهای جدید اضافه می‌شود.</p>
                    <a href="{{ route('portfolio.index') }}" class="btn btn-secondary mt-5">مشاهده همه پروژه‌ها</a>
                </div>
            @endif
        </div>
    </section>

    @include('front.t-one.partials.cta-band', ['title' => 'پروژه بعدی را با هم بسازیم؟', 'text' => 'ایده‌تان را برایمان بنویسید؛ پیشنهاد فنی و برآورد هزینه را ارسال می‌کنیم.'])
@endcomponent
