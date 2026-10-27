@php
    $img = \App\View\Seo::img($post->featured_image);
    $cat = $post->categories->first();
    $excerpt = \App\View\Seo::excerpt($post->meta_description ?: $post->body, 130);
    $date = ($post->published_at ?? $post->created_at) ? \Illuminate\Support\Carbon::parse($post->published_at ?? $post->created_at) : null;
@endphp
<article class="group card card-hover relative flex h-full flex-col overflow-hidden">
    <div class="relative aspect-[16/9] overflow-hidden border-b border-slate-100 bg-slate-100">
        @if($img)
            <img src="{{ $img }}" alt="{{ $post->alt ?: $post->title }}" width="1200" height="675" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        @else
            <div class="flex h-full w-full items-center justify-center text-4xl text-slate-300" aria-hidden="true"><i class="ph ph-article"></i></div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <div class="mb-2 flex items-center gap-2 text-xs">
            @if($cat)<span class="chip chip-blue">{{ $cat->name }}</span>@endif
            <time class="text-slate-500" datetime="{{ $date?->toDateString() }}">{{ $date ? \App\Helpers\Fa::toFa(jdate($date)->format("%d %B %Y")) : "" }}</time>
        </div>
        <h3 class="line-clamp-2 text-[1.0625rem] font-bold leading-8 text-slate-900" dir="auto">
            <a href="{{ route('blog.show', $post->slug) }}" class="after:absolute after:inset-0 after:content-['']">{{ $post->title }}</a>
        </h3>
        @if($excerpt)<p class="mt-1.5 line-clamp-3 text-sm leading-7 text-slate-600">{{ $excerpt }}</p>@endif
        <span class="link-arrow mt-auto pt-3" aria-hidden="true">ادامه مطلب <i class="ph ph-arrow-left"></i></span>
    </div>
</article>
