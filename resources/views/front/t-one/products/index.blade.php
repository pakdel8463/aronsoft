@php
    use App\View\Seo;
    $type = $type ?? null;
    $types = ['wordpress_theme', 'wordpress_plugin', 'laravel_package', 'linux_script'];
    $label = $type ? Seo::productType($type) : null;
    $url = $type ? route('products.filter', $type) : route('products.index');
    Seo::meta([
        'title' => $label ? 'خرید '.$label.' | محصولات دیجیتال' : 'فروشگاه محصولات دیجیتال',
        'description' => 'قالب وردپرس، افزونه، پکیج لاراول و اسکریپت‌های آماده برای توسعه‌دهندگان و کسب‌وکارها از تیم آرون سافت.',
        'canonical' => $url,
    ]);
    $crumbs = [['خانه', url('/')], ['فروشگاه', $type ? route('products.index') : null]];
    if ($type) { $crumbs[] = [$label, null]; }
@endphp
@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd(Seo::breadcrumbs(collect($crumbs)->map(fn ($c) => [$c[0], $c[1] ?: $url])->all())) !!}
    @endslot
    @include('front.t-one.partials.page-header', ['crumbs' => $crumbs, 'title' => $label ?: 'محصولات دیجیتال', 'lead' => 'ابزارهای تخصصی، قالب‌های وردپرس و پکیج‌های لاراول برای توسعه‌دهندگان و کسب‌وکارها.'])

    <section class="section" aria-label="فهرست محصولات">
        <div class="container-x">
            <nav class="mb-8 flex flex-wrap gap-2" aria-label="نوع محصول">
                <a href="{{ route('products.index') }}" class="chip-filter {{ !$type ? 'is-active' : '' }}" @if(!$type) aria-current="page" @endif>همه محصولات</a>
                @foreach($types as $t)<a href="{{ route('products.filter', $t) }}" class="chip-filter {{ $type === $t ? 'is-active' : '' }}" @if($type === $t) aria-current="page" @endif>{{ Seo::productType($t) }}</a>@endforeach
            </nav>
            @if($products->count())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach($products as $product)@include('front.t-one.partials.product-card', ['product' => $product])@endforeach</div>
                <div class="mt-10">{{ $products->links() }}</div>
            @else
                <div class="card mx-auto max-w-lg p-10 text-center">
                    <span class="icon-tile mx-auto mb-4"><i class="ph ph-shopping-bag-open" aria-hidden="true"></i></span>
                    <h2 class="text-lg font-bold text-slate-900">محصولی در این دسته یافت نشد</h2>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary mt-5">مشاهده همه محصولات</a>
                </div>
            @endif
        </div>
    </section>
@endcomponent
