<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * تبدیل «شرح پروژه» (HTML ویرایشگر) به فصل‌های یک مطالعه‌ی موردی برای صفحه‌ی نمونه‌کار.
 *
 * قرارداد نویسندگی (همه‌چیز در همان ویرایشگر معمولی):
 *  - هر تیتر H2 = یک فصل. متن قبل از اولین H2 = فصل «درباره‌ی پروژه».
 *  - لیست معمولی در فصل  = کارت‌های ویژگی.
 *  - لیستی که همه‌ی آیتم‌هایش «مقدار | عنوان» باشد (مثلاً «۳ ماه | زمان اجرا») = کارت‌های عدد.
 *  - نقل‌قول (blockquote) = کارت نظر کارفرما.
 * اگر هیچ H2ای نباشد، کل متن یک فصل ساده می‌شود (محتوای قدیمی خراب نمی‌شود).
 */
class CaseStudy
{
    /** @return array<int, array{title:string,prose:string,features:array,stats:array,quotes:array}> */
    public static function chapters(?string $html): array
    {
        $html = trim((string) $html);
        if ($html === '') {
            return [];
        }

        $dom = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="cs-root">'.$html.'</div>', LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = (new \DOMXPath($dom))->query('//div[@id="cs-root"]')->item(0);
        if (! $root) {
            return [self::blank('درباره‌ی پروژه', $html)];
        }

        $chapters = [];
        $cur = self::blank('درباره‌ی پروژه');
        $hasH2 = false;

        foreach (iterator_to_array($root->childNodes) as $node) {
            if ($node instanceof \DOMElement && strtolower($node->nodeName) === 'h2') {
                if (self::filled($cur)) {
                    $chapters[] = $cur;
                }
                $hasH2 = true;
                $cur = self::blank(trim(preg_replace('/\s+/u', ' ', $node->textContent)) ?: 'فصل');

                continue;
            }
            self::absorb($cur, $node, $dom);
        }
        if (self::filled($cur)) {
            $chapters[] = $cur;
        }

        return $chapters;
    }

    /** رنگ شاخص پروژه از روی تصویر شاخص (پررنگ‌ترین رنگ، با روشنایی کنترل‌شده). پیش‌فرض: آبی برند. */
    public static function accent(?string $relativePath, string $fallback = '#2f4ff5', int $maxLum = 150): string
    {
        if (! $relativePath || preg_match('#^(https?:)?//#', $relativePath) || ! function_exists('imagecreatefromstring')) {
            return $fallback;
        }
        $file = public_path(ltrim($relativePath, '/'));
        if (! is_file($file)) {
            return $fallback;
        }

        return Cache::remember('cs.accent.'.$maxLum.'.'.md5($file.filemtime($file)), 86400 * 30, function () use ($file, $fallback, $maxLum) {
            try {
                $src = @imagecreatefromstring((string) file_get_contents($file));
                if (! $src) {
                    return $fallback;
                }
                $s = imagecreatetruecolor(24, 24);
                imagecopyresampled($s, $src, 0, 0, 0, 0, 24, 24, imagesx($src), imagesy($src));
                $best = [];
                for ($x = 0; $x < 24; $x++) {
                    for ($y = 0; $y < 24; $y++) {
                        $c = imagecolorat($s, $x, $y);
                        [$r, $g, $b] = [($c >> 16) & 255, ($c >> 8) & 255, $c & 255];
                        $max = max($r, $g, $b);
                        $min = min($r, $g, $b);
                        $sat = $max ? ($max - $min) / $max : 0;
                        $val = $max / 255;
                        if ($sat > 0.45 && $val > 0.3) {
                            $best[] = [$sat * $val, $r, $g, $b];
                        }
                    }
                }
                if (count($best) < 6) {
                    return $fallback;
                }
                usort($best, fn ($a, $b) => $b[0] <=> $a[0]);
                $top = array_slice($best, 0, 14);
                $r = (int) (array_sum(array_column($top, 1)) / count($top));
                $g = (int) (array_sum(array_column($top, 2)) / count($top));
                $b = (int) (array_sum(array_column($top, 3)) / count($top));
                // تیره‌کردن تا حداقل کنتراست روی سفید
                $lum = fn ($r, $g, $b) => 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
                while ($lum($r, $g, $b) > $maxLum) {
                    [$r, $g, $b] = [(int) ($r * .92), (int) ($g * .92), (int) ($b * .92)];
                }

                return sprintf('#%02x%02x%02x', $r, $g, $b);
            } catch (\Throwable $e) {
                return $fallback;
            }
        });
    }

    private static function blank(string $title, string $prose = ''): array
    {
        return ['title' => $title, 'prose' => $prose, 'features' => [], 'stats' => [], 'quotes' => []];
    }

    private static function filled(array $c): bool
    {
        return trim(strip_tags($c['prose'])) !== '' || $c['features'] || $c['stats'] || $c['quotes'];
    }

    private static function absorb(array &$c, \DOMNode $node, \DOMDocument $dom): void
    {
        if ($node instanceof \DOMText) {
            if (trim($node->textContent) !== '') {
                $c['prose'] .= '<p>'.e($node->textContent).'</p>';
            }

            return;
        }
        if (! $node instanceof \DOMElement) {
            return;
        }
        $name = strtolower($node->nodeName);

        if ($name === 'ul' || $name === 'ol') {
            $items = [];
            foreach ($node->childNodes as $li) {
                if ($li instanceof \DOMElement && strtolower($li->nodeName) === 'li') {
                    $inner = '';
                    foreach ($li->childNodes as $ch) {
                        $inner .= $dom->saveHTML($ch);
                    }
                    $inner = trim($inner);
                    if (trim(strip_tags($inner)) !== '') {
                        $items[] = $inner;
                    }
                }
            }
            if (! $items) {
                return;
            }
            $allStats = true;
            foreach ($items as $it) {
                if (! str_contains(strip_tags($it), '|')) {
                    $allStats = false;
                    break;
                }
            }
            if ($allStats) {
                foreach ($items as $it) {
                    [$v, $l] = array_map('trim', explode('|', strip_tags($it), 2));
                    $c['stats'][] = ['value' => $v, 'label' => $l];
                }
            } else {
                foreach ($items as $it) {
                    $c['features'][] = $it;
                }
            }

            return;
        }

        if ($name === 'blockquote') {
            $t = trim(preg_replace('/\s+/u', ' ', $node->textContent));
            if ($t !== '') {
                $c['quotes'][] = $t;
            }

            return;
        }

        $out = trim($dom->saveHTML($node));
        if ($out !== '' && trim(strip_tags($out, '<img><iframe><video>')) !== '') {
            $c['prose'] .= $out;
        }
    }
}
