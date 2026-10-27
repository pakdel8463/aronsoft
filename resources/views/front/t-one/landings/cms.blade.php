@php
    \App\View\Seo::meta(['title'=>$landing->seo_title ?: $landing->title, 'description'=>$landing->seo_description, 'canonical'=>url('/services/'.$landing->slug)]);
    $colors = $landing->appearance ?? [];
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', $colors['accent'] ?? '') ? $colors['accent'] : '#4959d5';
    $bg = preg_match('/^#[0-9a-fA-F]{6}$/', $colors['background'] ?? '') ? $colors['background'] : '#f7f8fc';
    $fg = preg_match('/^#[0-9a-fA-F]{6}$/', $colors['foreground'] ?? '') ? $colors['foreground'] : '#16213d';
@endphp
@component('front.t-one.layouts.content')
<div dir="rtl" style="--lp-accent:{{ $accent }};background:{{ $bg }};color:{{ $fg }}" class="min-h-screen">
@if($preview ?? false)<div class="sticky top-0 z-50 bg-amber-100 text-amber-900 text-center p-3 font-bold">پیش‌نمایش نسخه پیش‌نویس — هنوز منتشر نشده است</div>@endif
@foreach($sections as $s)
@if(!($s['enabled'] ?? true)) @continue @endif
@php($type=$s['type'] ?? 'text')
@if($type==='hero')
<section class="mx-auto max-w-7xl px-5 py-20 md:py-28 grid gap-12 lg:grid-cols-2 items-center"><div><span class="text-sm font-bold" style="color:var(--lp-accent)">{{ $s['subtitle'] ?? '' }}</span><h1 class="text-4xl md:text-6xl font-black leading-tight mt-4">{{ $s['title'] ?? $landing->title }}</h1><p class="mt-6 text-lg leading-9 opacity-75 whitespace-pre-line">{{ $s['body'] ?? '' }}</p>@if(!empty($s['button_url']))<a class="inline-flex mt-8 px-7 py-4 rounded-xl text-white font-bold" style="background:var(--lp-accent)" href="{{ $s['button_url'] }}" rel="noopener">{{ $s['button_text'] ?? 'شروع کنید' }}</a>@endif</div><div>@if(!empty($s['image']))<img class="w-full rounded-3xl shadow-2xl" loading="eager" src="{{ $s['image'] }}" alt="{{ $s['title'] ?? $landing->title }}">@endif</div></section>
@elseif($type==='features' || $type==='stats' || $type==='faq')
<section class="mx-auto max-w-7xl px-5 py-16"><h2 class="text-3xl md:text-4xl font-extrabold mb-4">{{ $s['title'] ?? '' }}</h2><p class="opacity-65 mb-8">{{ $s['subtitle'] ?? '' }}</p><div class="grid md:grid-cols-3 gap-5">@foreach(($s['items'] ?? []) as $item)<div class="rounded-2xl bg-white p-7 shadow-sm leading-8" style="color:#16213d">{{ $item }}</div>@endforeach</div></section>
@elseif($type==='gallery')
<section class="mx-auto max-w-7xl px-5 py-16"><h2 class="text-3xl font-extrabold mb-8">{{ $s['title'] ?? '' }}</h2><div class="grid md:grid-cols-2 gap-6">@foreach(($s['items'] ?? []) as $img)<img src="{{ $img }}" loading="lazy" alt="{{ $s['title'] ?? $landing->title }}" class="w-full rounded-2xl shadow-lg">@endforeach</div></section>
@elseif($type==='portfolio')
@if($landing->portfolio && $landing->portfolio->status==='published')<section class="mx-auto max-w-7xl px-5 py-16"><h2 class="text-3xl font-extrabold mb-5">{{ $s['title'] ?? 'درباره پروژه' }}</h2><p class="leading-9 opacity-75">{{ $landing->portfolio->short_description }}</p><a class="inline-block mt-5 font-bold underline" style="color:var(--lp-accent)" href="{{ route('portfolio.show',$landing->portfolio) }}">مشاهده نمونه‌کار</a></section>@endif
@elseif($type==='cta')
<section class="mx-auto max-w-6xl px-5 py-16 text-center"><div class="rounded-3xl p-12 text-white" style="background:var(--lp-accent)"><h2 class="text-3xl font-black">{{ $s['title'] ?? '' }}</h2><p class="mt-5 opacity-90">{{ $s['body'] ?? '' }}</p>@if(!empty($s['button_url']))<a class="inline-block mt-8 rounded-xl bg-white px-8 py-3 font-bold" style="color:var(--lp-accent)" href="{{ $s['button_url'] }}">{{ $s['button_text'] ?? 'مشاهده محصول' }}</a>@endif</div></section>
@else
<section class="mx-auto max-w-5xl px-5 py-16"><h2 class="text-3xl font-extrabold mb-6">{{ $s['title'] ?? '' }}</h2><p class="whitespace-pre-line leading-9 opacity-75">{{ $s['body'] ?? '' }}</p>@if(!empty($s['image']))<img class="mt-8 rounded-2xl w-full" loading="lazy" src="{{ $s['image'] }}" alt="{{ $s['title'] ?? '' }}">@endif</section>
@endif
@endforeach
</div>
@endcomponent
