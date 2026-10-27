<?php

namespace App\View;

use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\TwitterCard;
use Illuminate\Support\Str;

/**
 * کمک‌کننده‌ی سئوی ویوهای فرانت: متای صفحه (روی artesaos/seotools) و JSON-LD.
 * استفاده در Blade:  @php \App\View\Seo::meta([...]) @endphp   و   {!! \App\View\Seo::jsonLd([...]) !!}
 */
class Seo
{
    public const SITE = 'آرون سافت';

    public static function meta(array $d): void
    {
        $title = $d['title'] ?? null;
        if ($title) {
            SEOMeta::setTitle($title);
            OpenGraph::setTitle($title);
            TwitterCard::setTitle($title);
        }
        if (! empty($d['description'])) {
            $desc = Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $d['description']))), 300, '');
            SEOMeta::setDescription($desc);
            OpenGraph::setDescription($desc);
            TwitterCard::setDescription($desc);
        }
        $canonical = $d['canonical'] ?? url()->current();
        SEOMeta::setCanonical($canonical);
        OpenGraph::setUrl($canonical);
        OpenGraph::setType($d['type'] ?? 'website');
        OpenGraph::addProperty('locale', 'fa_IR');
        if (! empty($d['image'])) {
            OpenGraph::addImage($d['image']);
            TwitterCard::setImage($d['image']);
        }
        if (! empty($d['noindex'])) {
            SEOMeta::setRobots('noindex, follow');
        }
    }

    /** خروجی script برای JSON-LD (یک شیء یا آرایه‌ای از اشیاء → @graph). */
    public static function jsonLd(array $data): string
    {
        if (array_is_list($data)) {
            $data = ['@context' => 'https://schema.org', '@graph' => $data];
        } else {
            $data = ['@context' => 'https://schema.org'] + $data;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);

        return '<script type="application/ld+json">'.$json.'</script>';
    }

    public static function organization(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => 'آرون سافت (Aron Soft)',
            'alternateName' => 'Aron Soft',
            'url' => url('/'),
            'logo' => asset('images/android-icon-192x192.png'),
            'email' => 'info@aron-soft.com',
            'telephone' => '+982122451912',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'بلوار ارتش، کوچه نصر ۲، پلاک ۸، واحد ۸',
                'addressLocality' => 'تهران',
                'addressCountry' => 'IR',
            ],
            'sameAs' => [
                'https://www.instagram.com/aron.soft/',
                'https://t.me/aronsoft',
            ],
        ];
    }

    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'url' => url('/'),
            'name' => 'آرون سافت',
            'inLanguage' => 'fa-IR',
            'publisher' => ['@id' => url('/').'#organization'],
        ];
    }

    /** @param array<int, array{0:string,1:?string}> $items [[نام، آدرس], ...] */
    public static function breadcrumbs(array $items): array
    {
        $list = [];
        foreach (array_values($items) as $i => [$name, $url]) {
            $el = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name];
            if ($url) {
                $el['item'] = $url;
            }
            $list[] = $el;
        }

        return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
    }

    /** @param array<int, array{0:string,1:string}> $faqs [[سؤال، پاسخ], ...] */
    public static function faq(array $faqs): array
    {
        return [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
            ], $faqs),
        ];
    }

    public static function article($post, ?string $url = null): array
    {
        $url ??= url()->current();
        $published = ($post->published_at ?? $post->created_at) ? \Illuminate\Support\Carbon::parse($post->published_at ?? $post->created_at) : null;

        return [
            '@type' => 'Article',
            'headline' => Str::limit($post->title, 110, ''),
            'description' => Str::limit(trim(strip_tags((string) ($post->meta_description ?: $post->body))), 200, ''),
            'image' => $post->featured_image ? [asset($post->featured_image)] : [asset('images/og-default.png')],
            'datePublished' => optional($published)->toIso8601String(),
            'dateModified' => $post->updated_at ? \Illuminate\Support\Carbon::parse($post->updated_at)->toIso8601String() : null,
            'inLanguage' => 'fa-IR',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'author' => ['@type' => 'Person', 'name' => optional($post->author)->name ?: 'تیم آرون سافت'],
            'publisher' => ['@id' => url('/').'#organization'],
        ];
    }

    public static function service(string $name, string $description, ?string $url = null, ?int $lowPriceToman = null): array
    {
        $s = [
            '@type' => 'Service',
            'name' => $name,
            'description' => $description,
            'url' => $url ?? url()->current(),
            'areaServed' => 'IR',
            'provider' => ['@id' => url('/').'#organization'],
        ];
        if ($lowPriceToman) {
            $s['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => 'IRR',
                'price' => (string) ($lowPriceToman * 10), // تومان → ریال
                'availability' => 'https://schema.org/InStock',
            ];
        }

        return $s;
    }

    /** خلاصه‌ی متن برای کارت‌ها (بدون تگ). */
    public static function excerpt(?string $text, int $limit = 140): string
    {
        return Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text))), $limit);
    }

    /** آدرس عمومی تصویر: مسیر نسبی → asset()، آدرس کامل/روت-مطلق دست‌نخورده. */
    public static function img(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (preg_match('#^(https?:)?//#', $path)) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    /** لیست تکنولوژی‌ها از رشته‌ی «A, B, C» یا JSON آرایه. */
    public static function techList($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }
        $value = (string) $value;
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('trim', array_map('strval', $decoded))));
        }

        return array_values(array_filter(array_map('trim', preg_split('/[,،]/u', $value))));
    }

    /** برچسب فارسی نوع محصول. */
    public static function productType(?string $type): string
    {
        return [
            'wordpress_theme' => 'قالب وردپرس',
            'wordpress_plugin' => 'افزونه وردپرس',
            'laravel_package' => 'پکیج لاراول',
            'linux_script' => 'اسکریپت لینوکس',
        ][$type ?? ''] ?? str_replace('_', ' ', (string) $type);
    }

    /** قیمت محصول: «رایگان» یا مبلغ با ارقام فارسی. */
    public static function productPrice($product): string
    {
        return $product->price > 0 ? \App\Helpers\Fa::toFa(number_format($product->price)).' تومان' : 'رایگان';
    }
}
