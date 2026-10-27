@php
    $img = \App\View\Seo::img($p->featured_image);
    $techs = array_slice(\App\View\Seo::techList($p->technologies), 0, 3);
@endphp
<article class="group card card-hover relative flex h-full flex-col overflow-hidden">
    <div class="relative aspect-[4/3] overflow-hidden border-b border-slate-100 bg-slate-100">
        @if($img)
            <img src="{{ $img }}" alt="تصویر پروژه {{ $p->title }}" width="1200" height="800" loading="lazy" decoding="async"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        @else
            <div class="flex h-full w-full items-center justify-center text-4xl text-slate-300" aria-hidden="true"><i class="ph ph-browsers"></i></div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-[1.0625rem] font-bold leading-8 text-slate-900" dir="auto">
            <a href="{{ route('portfolio.show', $p->slug) }}" class="after:absolute after:inset-0 after:content-[''] focus-visible:outline-none">{{ $p->title }}</a>
        </h3>
        @if($p->short_description)
            <p class="mt-1.5 line-clamp-2 text-sm leading-7 text-slate-600" dir="auto">{{ $p->short_description }}</p>
        @endif
        @if($techs)
            <ul class="mt-auto flex flex-wrap gap-1.5 pt-4" dir="ltr" aria-label="تکنولوژی‌ها">
                @foreach($techs as $t)<li class="chip">{{ $t }}</li>@endforeach
            </ul>
        @endif
    </div>
</article>
