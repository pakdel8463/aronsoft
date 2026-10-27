@php
    use App\View\Seo;
    $url = route('blog.category', $category->slug);
    Seo::meta([
        'title' => 'مقالات '.$category->name,
        'description' => Seo::excerpt($category->description, 160) ?: 'مقالات دسته‌ی '.$category->name.' در وبلاگ آرون سافت.',
        'canonical' => $url,
    ]);
    $crumbs = [['خانه', url('/')], ['وبلاگ', route('blog.index')], [$category->name, null]];
@endphp
@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd(Seo::breadcrumbs([['خانه', url('/')], ['وبلاگ', route('blog.index')], [$category->name, $url]])) !!}
    @endslot
    @include('front.t-one.partials.page-header', ['crumbs' => $crumbs, 'eyebrow' => 'دسته‌بندی', 'title' => $category->name, 'lead' => $category->description ?: null])
    @include('front.t-one.posts._list')
@endcomponent
