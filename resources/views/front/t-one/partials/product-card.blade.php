@php $img = \App\View\Seo::img($product->featured_image); @endphp
<article class="group card card-hover relative flex h-full flex-col overflow-hidden">
    <div class="relative aspect-[4/3] overflow-hidden border-b border-slate-100 bg-slate-100">
        @if($img)<img src="{{ $img }}" alt="{{ $product->title }}" width="1200" height="900" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        @else<div class="flex h-full items-center justify-center text-4xl text-slate-300" aria-hidden="true"><i class="ph ph-package"></i></div>@endif
        <span class="chip chip-blue absolute start-3 top-3 bg-white/95">{{ \App\View\Seo::productType($product->type) }}</span>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <div class="mb-2 flex items-center gap-2 text-xs text-slate-500">
            @if($product->version)<span class="chip">نسخه <span class="num" dir="ltr">{{ $product->version }}</span></span>@endif
            <time datetime="{{ $product->updated_at?->toDateString() }}">{{ \App\Helpers\Fa::toFa(jdate($product->updated_at)->format('%d %B %Y')) }}</time>
        </div>
        <h3 class="text-[1.0625rem] font-bold leading-8 text-slate-900" dir="auto"><a href="{{ route('products.show', $product->slug) }}" class="after:absolute after:inset-0 after:content-['']">{{ $product->title }}</a></h3>
        @if($product->short_description)<p class="mt-1.5 line-clamp-2 text-sm leading-7 text-slate-600">{{ $product->short_description }}</p>@endif
        <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-4">
            <span class="text-base font-extrabold {{ $product->price > 0 ? 'text-slate-900' : 'text-emerald-600' }}">{{ \App\View\Seo::productPrice($product) }}</span>
            <span class="link-arrow" aria-hidden="true">جزئیات <i class="ph ph-arrow-left"></i></span>
        </div>
    </div>
</article>
