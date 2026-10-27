@php
    use App\View\Seo;
    $crumbs = [['خانه', url('/')], ['وبلاگ', null]];
@endphp
@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd([Seo::breadcrumbs([['خانه', url('/')], ['وبلاگ', route('blog.index')]]), ['@type' => 'Blog', 'name' => 'وبلاگ آرون سافت', 'url' => route('blog.index'), 'inLanguage' => 'fa-IR', 'publisher' => ['@id' => url('/').'#organization']]]) !!}
        @if($posts->previousPageUrl())<link rel="prev" href="{{ $posts->previousPageUrl() }}">@endif
        @if($posts->nextPageUrl())<link rel="next" href="{{ $posts->nextPageUrl() }}">@endif
    @endslot
    @include('front.t-one.partials.page-header', ['crumbs' => $crumbs, 'title' => 'وبلاگ آرون سافت', 'lead' => 'مقالات تخصصی طراحی سایت، سئو، لاراول و دواپس؛ تجربه‌های واقعی از پروژه‌ها.'])
    @include('front.t-one.posts._list')
@endcomponent
