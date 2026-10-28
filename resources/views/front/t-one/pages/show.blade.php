@php
    use App\View\Seo;
    Seo::meta(array_filter([
        'title' => $page->setting('meta_title', $page->title),
        'description' => $page->setting('meta_description'),
        'image' => \App\Support\ProductPageSchema::imageUrl($page->setting('og_image')),
        'canonical' => $preview ? null : $page->url(),
        'noindex' => $preview || $page->setting('noindex'),
    ]));
@endphp

@component('front.t-one.layouts.content')
    @slot('head')
        @if ($page->is_home)
            {!! Seo::jsonLd([Seo::organization(), Seo::website()]) !!}
        @endif
    @endslot

    @foreach ($blocks as $block)
        @include('front.t-one.blocks.'.$block->type, [
            's' => $block->content(),
            'block' => $block,
            'anchor' => $block->anchorId(),
            'first' => $loop->first,
        ])
    @endforeach

    @if ($blocks->isEmpty())
        <div class="container-x py-24 text-center text-slate-500">
            <i class="ph ph-layout mb-3 block text-5xl text-slate-300" aria-hidden="true"></i>
            {{ $preview ? 'این صفحه هنوز بلوکی ندارد؛ از صفحه‌ساز بلوک اضافه کنید.' : 'این صفحه به‌زودی تکمیل می‌شود.' }}
        </div>
    @endif

    @if ($preview && ! $embed)
        <div class="fixed inset-x-3 bottom-3 z-[60] mx-auto flex max-w-xl items-center justify-between gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-sm text-white shadow-card">
            <span class="flex items-center gap-2"><i class="ph ph-eye text-lg text-blue-300" aria-hidden="true"></i>
                {{ $page->is_template ? 'پیش‌نمایش قالب' : ($page->is_active ? 'پیش‌نمایش صفحه' : 'پیش‌نمایش؛ این صفحه هنوز منتشر نشده') }}</span>
            <a href="{{ route('panel.pages.builder', $page) }}" class="font-bold text-blue-300 hover:text-white">بازگشت به صفحه‌ساز</a>
        </div>
    @endif
@endcomponent
