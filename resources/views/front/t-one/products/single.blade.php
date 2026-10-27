@php
    use App\View\Seo;
    use App\Helpers\Fa;
    $img = Seo::img($product->featured_image);
    $crumbs = [['خانه', url('/')], ['فروشگاه', route('products.index')], [$product->title, null]];
    Seo::meta(['title' => $product->seo_title ?: $product->title, 'description' => $product->seo_description ?: Seo::excerpt($product->short_description ?: $product->description, 160), 'canonical' => $product->canonical_url ?: route('products.show', $product->slug), 'image' => $img]);
    $ld = ['@type' => 'Product', 'name' => $product->title, 'description' => Seo::excerpt($product->short_description ?: $product->description, 200), 'url' => route('products.show', $product->slug), 'brand' => ['@type' => 'Brand', 'name' => 'آرون سافت']];
    if ($img) { $ld['image'] = $img; }
    if ($product->is_for_sale || $product->price > 0) {
        $ld['offers'] = ['@type' => 'Offer', 'priceCurrency' => 'IRR', 'price' => (string) ((int) $product->price * 10), 'availability' => 'https://schema.org/InStock', 'url' => route('products.show', $product->slug)];
    }
@endphp
@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd([Seo::breadcrumbs(collect($crumbs)->map(fn ($c) => [$c[0], $c[1] ?: route('products.show', $product->slug)])->all()), $ld]) !!}
    @endslot

    <div class="container-x pt-8">@include('front.t-one.partials.breadcrumb', ['items' => $crumbs])</div>

    <section class="pb-12 md:pb-16">
        <div class="container-x grid gap-8 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-8">
                @if($img)<img src="{{ $img }}" alt="{{ $product->title }}" width="1200" height="900" fetchpriority="high" class="aspect-[4/3] w-full rounded-2xl border border-slate-200 object-cover sm:aspect-[16/9]">@endif
                <h2 class="h2-section mb-4 mt-10">معرفی و جزئیات</h2>
                <div class="rich">{!! $product->description !!}</div>
            </div>

            <aside class="lg:col-span-4" aria-label="خرید و مشخصات">
                <div class="space-y-5 lg:sticky lg:top-24">
                    <div class="card p-6">
                        <span class="chip chip-blue">{{ Seo::productType($product->type) }}</span>
                        <h1 class="mt-3 text-xl font-extrabold leading-9 text-slate-900" dir="auto">{{ $product->title }}</h1>
                        @if($product->short_description)<p class="mt-2 text-sm leading-7 text-slate-600">{{ $product->short_description }}</p>@endif
                        <div class="my-5 border-y border-slate-100 py-4">
                            <span class="block text-xs text-slate-500">قیمت</span>
                            <span class="text-2xl font-extrabold {{ $product->price > 0 ? 'text-slate-900' : 'text-emerald-600' }}">{{ Seo::productPrice($product) }}</span>
                        </div>
                        @if($product->is_for_sale)
                            <a href="{{ url('/contact') }}?subject={{ urlencode('خرید '.$product->title) }}" class="btn btn-primary btn-lg btn-block"><i class="ph ph-shopping-cart" aria-hidden="true"></i> استعلام و خرید</a>
                        @endif
                        @if($product->demo_link)<a href="{{ $product->demo_link }}" target="_blank" rel="noopener nofollow" class="btn btn-secondary btn-block mt-3"><i class="ph ph-arrow-square-out" aria-hidden="true"></i> پیش‌نمایش زنده</a>@endif
                    </div>
                    <div class="card p-6">
                        <h2 class="mb-3 text-base font-bold text-slate-900">مشخصات</h2>
                        <dl class="divide-y divide-slate-100 text-sm">
                            @if($product->version)<div class="flex justify-between py-2.5"><dt class="text-slate-500">نسخه</dt><dd class="font-semibold text-slate-900 num" dir="ltr">{{ $product->version }}</dd></div>@endif
                            <div class="flex justify-between py-2.5"><dt class="text-slate-500">آخرین بروزرسانی</dt><dd class="font-semibold text-slate-900">{{ Fa::toFa(jdate($product->updated_at)->format('%d %B %Y')) }}</dd></div>
                            <div class="flex justify-between py-2.5"><dt class="text-slate-500">پشتیبانی</dt><dd class="font-semibold text-slate-900">آرون سافت</dd></div>
                            @if(($product->download_count ?? 0) > 0)<div class="flex justify-between py-2.5"><dt class="text-slate-500">دریافت</dt><dd class="font-semibold text-slate-900">{{ Fa::toFa($product->download_count) }}</dd></div>@endif
                        </dl>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    @if(isset($relatedProducts) && $relatedProducts->count())
        <section class="section section-alt" aria-labelledby="rel-p">
            <div class="container-x">
                <h2 id="rel-p" class="h2-section mb-6">محصولات مشابه</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach($relatedProducts as $rp)@include('front.t-one.partials.product-card', ['product' => $rp])@endforeach</div>
            </div>
        </section>
    @endif
@endcomponent
