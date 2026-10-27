@php
    use App\View\Seo;
    use App\Support\ProductPageSchema;
    /** @var \App\Models\ProductPage $page */
    $hero = $page->section('hero') + ['subtitle' => '', 'image' => ''];
    $faq = $page->section('faq');
    $url = $page->publicUrl();

    $title = $page->seo_title ?: trim($page->name.($page->tagline ? ' — '.$page->tagline : ''));
    $desc = $page->seo_description ?: ($hero['subtitle'] ?: ($page->tagline ?: $page->name));
    $ogImage = ProductPageSchema::imageUrl($page->og_image ?: ($hero['image'] ?: ($page->card_image ?: $page->logo)));

    Seo::meta([
        'title' => $title,
        'description' => $desc,
        'canonical' => $page->canonical_url ?: $url,
        'image' => $ogImage,
        'noindex' => $page->noindex || $preview,
    ]);
    if ($page->seo_keywords) {
        \Artesaos\SEOTools\Facades\SEOMeta::setKeywords(array_map('trim', explode(',', $page->seo_keywords)));
    }

    $links = $page->filledLinks();
    $app = [
        '@type' => 'SoftwareApplication',
        'name' => $page->name,
        'url' => $url,
        'description' => Seo::excerpt($desc, 300),
        'applicationCategory' => 'BusinessApplication',
        'inLanguage' => 'fa-IR',
        'publisher' => ['@id' => url('/').'#organization'],
    ];
    if ($page->name_en) { $app['alternateName'] = $page->name_en; }
    if ($img = $ogImage) { $app['image'] = $img; }
    $os = [];
    if (isset($links['android']) || isset($links['bazaar']) || isset($links['myket']) || isset($links['google_play'])) { $os[] = 'Android'; }
    if (isset($links['ios'])) { $os[] = 'iOS'; }
    if (isset($links['website']) || isset($links['login'])) { $os[] = 'Web'; }
    if ($os) { $app['operatingSystem'] = implode(', ', $os); }

    $graph = [
        Seo::breadcrumbs([['خانه', url('/')], ['محصولات', route('product-pages.index')], [$page->name, $url]]),
        $app,
    ];
    $faqRows = collect($faq['items'] ?? [])->filter(fn ($r) => ($r['q'] ?? '') !== '' && ($r['a'] ?? '') !== '')->map(fn ($r) => [$r['q'], $r['a']])->values()->all();
    if ($faqRows && in_array('faq', $page->layoutKeys(), true)) { $graph[] = Seo::faq($faqRows); }

    $keys = $page->layoutKeys();
@endphp

@component('front.t-one.layouts.content')
    @slot('head')
        {!! Seo::jsonLd($graph) !!}
    @endslot

    @if($preview)
        <div class="border-b border-amber-200 bg-amber-50 px-4 py-2.5 text-center text-sm font-bold text-amber-800" role="status">
            پیش‌نمایش پیش‌نویس — این صفحه هنوز منتشر نشده و برای عموم و موتورهای جستجو دیده نمی‌شود.
            <a href="{{ route('panel.product-pages.edit', $page) }}" class="underline">ویرایش در پنل</a>
        </div>
    @endif

    {{-- بدنه‌ی صفحه: ویوی قالب انتخاب‌شده (templates/{key}.blade.php) --}}
    @include($page->templateView(), ['page' => $page, 'keys' => $keys, 'preview' => $preview])
@endcomponent
