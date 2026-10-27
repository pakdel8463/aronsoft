<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * پاک‌سازی HTML ویرایشگر با allow-list سخت‌گیرانه (HTMLPurifier روی پروژه نصب نیست).
 *  - تگ/ویژگی‌های غیرمجاز حذف می‌شوند، on*، javascript: و data: بیرون می‌ماند
 *  - iframe فقط از یوتیوب / آپارات / ویمئو (https)
 *  - style فقط با چند ویژگی ایمن، class/id/data-* با کاراکترهای امن
 *  - به h2–h4 بدون id، شناسه‌ی فارسی‌ایمن برای فهرست مطالب افزوده می‌شود
 */
class HtmlSanitizer
{
    /** تگ‌هایی که همراه محتوا حذف می‌شوند */
    private const DROP = ['script', 'style', 'object', 'embed', 'applet', 'form', 'input', 'textarea', 'select', 'option', 'meta', 'link', 'base', 'noscript', 'template', 'svg', 'math', 'frame', 'frameset', 'canvas', 'title', 'head'];

    private const ALLOWED = [
        'p', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'del', 'ins', 'sub', 'sup', 'mark', 'small',
        'blockquote', 'q', 'cite', 'pre', 'code', 'kbd', 'samp', 'var', 'abbr', 'time', 'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'a', 'img', 'figure', 'figcaption', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
        'div', 'span', 'section', 'article', 'aside', 'details', 'summary', 'iframe', 'video', 'audio', 'source', 'button',
    ];

    private const URL_ATTRS = ['href', 'src'];

    private const SCHEMES = ['http', 'https', 'mailto', 'tel'];

    private const IFRAME_HOSTS = [
        'www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com', 'www.aparat.com', 'aparat.com',
    ];

    private const STYLE_PROPS = [
        'text-align', 'color', 'background-color', 'font-size', 'font-weight', 'font-style', 'text-decoration', 'line-height',
        'width', 'max-width', 'height', 'float', 'direction', 'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
        'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right', 'border', 'border-radius', 'aspect-ratio', 'vertical-align',
    ];

    /** @var array<string,int> */
    private array $usedIds = [];

    public static function clean(?string $html, bool $headingIds = true): string
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        return (new self)->run($html, $headingIds);
    }

    private function run(string $html, bool $headingIds): string
    {
        $this->usedIds = [];
        $prev = libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="utf-8" ?><div id="aron-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $doc->getElementById('aron-root') ?: $doc->documentElement;
        if (! $root) {
            return '';
        }

        // شناسه‌های موجود را از قبل ثبت کن تا تکراری نشوند
        foreach ($root->getElementsByTagName('*') as $el) {
            if ($el->hasAttribute('id')) {
                $this->usedIds[$el->getAttribute('id')] = 1;
            }
        }

        $this->walk($root, $headingIds);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private function walk(DOMNode $parent, bool $headingIds): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
                $parent->removeChild($node);

                continue;
            }
            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, self::DROP, true)) {
                $parent->removeChild($node);

                continue;
            }

            if (! in_array($tag, self::ALLOWED, true)) {
                // ناشناخته: محتوا را نگه دار، تگ را بردار
                $this->walk($node, $headingIds);
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);

                continue;
            }

            if (! $this->cleanElement($node, $tag)) {
                $parent->removeChild($node);

                continue;
            }

            $this->walk($node, $headingIds);

            if ($headingIds && in_array($tag, ['h2', 'h3', 'h4'], true) && ! $node->hasAttribute('id')) {
                $text = trim($node->textContent);
                if ($text !== '') {
                    $base = Slugger::make($text, 80, 'section');
                    $id = $base;
                    $i = 2;
                    while (isset($this->usedIds[$id])) {
                        $id = $base.'-'.$i++;
                    }
                    $this->usedIds[$id] = 1;
                    $node->setAttribute('id', $id);
                }
            }
        }
    }

    /** false = المنت باید کلاً حذف شود */
    private function cleanElement(DOMElement $el, string $tag): bool
    {
        $keep = [];
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);
            $value = (string) $attr->nodeValue;
            $clean = $this->cleanAttr($tag, $name, $value);
            if ($clean !== null) {
                $keep[$name] = $clean;
            }
        }

        foreach (iterator_to_array($el->attributes) as $attr) {
            $el->removeAttribute($attr->nodeName);
        }

        if ($tag === 'iframe') {
            $src = $keep['src'] ?? '';
            if (! $this->iframeAllowed($src)) {
                return false;
            }
            $keep['src'] = preg_replace('#^http://#i', 'https://', $src) ?? $src;
            $keep['loading'] = 'lazy';
            $keep['allowfullscreen'] = 'allowfullscreen';
            $keep['referrerpolicy'] = 'strict-origin-when-cross-origin';
            $keep['allow'] = 'accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen';
            unset($keep['frameborder']);
        }

        if ($tag === 'img' && empty($keep['src'])) {
            return false;
        }

        if ($tag === 'a') {
            if (($keep['target'] ?? '') === '_blank') {
                $keep['rel'] = 'noopener noreferrer';
            } else {
                unset($keep['target']);
            }
        }

        if ($tag === 'button') {
            $keep['type'] = 'button';
        }

        foreach ($keep as $k => $v) {
            $el->setAttribute($k, $v);
        }

        return true;
    }

    private function cleanAttr(string $tag, string $name, string $value): ?string
    {
        if (str_starts_with($name, 'on') || $name === 'srcdoc' || str_contains($name, ':')) {
            return null;
        }

        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value) ?? '';

        switch ($name) {
            case 'class':
                $tokens = preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $tokens = array_filter($tokens, fn ($t) => preg_match('/^[A-Za-z0-9_\-:\/\.\[\]%]+$/', $t));

                return $tokens ? implode(' ', $tokens) : null;
            case 'id':
                return preg_match('/^[\p{L}\p{N}_\-]{1,100}$/u', $value) ? $value : null;
            case 'style':
                return $this->cleanStyle($value);
            case 'dir':
                return in_array(strtolower($value), ['rtl', 'ltr', 'auto'], true) ? strtolower($value) : null;
            case 'lang':
            case 'title':
            case 'aria-label':
            case 'aria-hidden':
            case 'role':
                return mb_substr($value, 0, 300);
        }

        if (str_starts_with($name, 'data-')) {
            return preg_match('/^data-[a-z0-9\-]+$/', $name) ? mb_substr($value, 0, 500) : null;
        }

        switch ($tag) {
            case 'a':
                if ($name === 'href') {
                    return $this->safeUrl($value, true);
                }
                if ($name === 'target') {
                    return in_array($value, ['_blank', '_self'], true) ? $value : null;
                }
                if ($name === 'rel' || $name === 'download' || $name === 'hreflang') {
                    return mb_substr($value, 0, 100);
                }
                break;
            case 'img':
                if ($name === 'src') {
                    return $this->safeUrl($value, false);
                }
                if ($name === 'srcset') {
                    return $this->safeSrcset($value);
                }
                if (in_array($name, ['alt', 'sizes'], true)) {
                    return mb_substr($value, 0, 500);
                }
                if (in_array($name, ['width', 'height'], true)) {
                    return preg_match('/^\d{1,5}%?$/', $value) ? $value : null;
                }
                if ($name === 'loading') {
                    return in_array($value, ['lazy', 'eager'], true) ? $value : null;
                }
                if ($name === 'decoding') {
                    return in_array($value, ['async', 'sync', 'auto'], true) ? $value : null;
                }
                break;
            case 'iframe':
                if ($name === 'src') {
                    return $this->safeUrl($value, false);
                }
                if (in_array($name, ['width', 'height'], true)) {
                    return preg_match('/^\d{1,4}%?$/', $value) ? $value : null;
                }
                break;
            case 'video':
            case 'audio':
            case 'source':
                if ($name === 'src') {
                    return $this->safeUrl($value, false);
                }
                if ($name === 'type') {
                    return preg_match('#^(video|audio)/[a-z0-9.+\-]+$#i', $value) ? $value : null;
                }
                if (in_array($name, ['controls', 'loop', 'muted', 'playsinline'], true)) {
                    return $name;
                }
                if ($name === 'preload') {
                    return in_array($value, ['none', 'metadata', 'auto'], true) ? $value : null;
                }
                if ($name === 'poster') {
                    return $this->safeUrl($value, false);
                }
                if (in_array($name, ['width', 'height'], true)) {
                    return preg_match('/^\d{1,4}%?$/', $value) ? $value : null;
                }
                break;
            case 'td':
            case 'th':
                if (in_array($name, ['colspan', 'rowspan'], true)) {
                    return preg_match('/^\d{1,3}$/', $value) ? $value : null;
                }
                if ($name === 'scope') {
                    return in_array($value, ['row', 'col', 'rowgroup', 'colgroup'], true) ? $value : null;
                }
                break;
            case 'col':
            case 'colgroup':
                if ($name === 'span') {
                    return preg_match('/^\d{1,3}$/', $value) ? $value : null;
                }
                break;
            case 'table':
                if (in_array($name, ['border', 'cellpadding', 'cellspacing'], true)) {
                    return preg_match('/^\d{1,3}$/', $value) ? $value : null;
                }
                if ($name === 'width') {
                    return preg_match('/^\d{1,4}(px|%)?$/', $value) ? $value : null;
                }
                break;
            case 'ol':
                if ($name === 'start') {
                    return preg_match('/^-?\d{1,5}$/', $value) ? $value : null;
                }
                if ($name === 'type') {
                    return in_array($value, ['1', 'a', 'A', 'i', 'I'], true) ? $value : null;
                }
                break;
            case 'time':
                if ($name === 'datetime') {
                    return mb_substr($value, 0, 40);
                }
                break;
            case 'details':
                if ($name === 'open') {
                    return 'open';
                }
                break;
        }

        return null;
    }

    private function safeUrl(string $value, bool $allowAnchors): ?string
    {
        $v = trim($value);
        if ($v === '') {
            return null;
        }
        $probe = preg_replace('/[\x00-\x20\x7F]+/', '', $v) ?? '';
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $probe, $m)) {
            return in_array(strtolower($m[1]), self::SCHEMES, true) ? $v : null;
        }
        if (str_starts_with($probe, '//')) {
            return 'https:'.$probe;
        }
        if (str_starts_with($probe, '#') && ! $allowAnchors) {
            return null;
        }

        return $v;
    }

    private function safeSrcset(string $value): ?string
    {
        $parts = [];
        foreach (explode(',', $value) as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '') {
                continue;
            }
            $bits = preg_split('/\s+/', $candidate);
            $url = $this->safeUrl($bits[0], false);
            if ($url === null) {
                continue;
            }
            $desc = $bits[1] ?? '';
            if ($desc !== '' && ! preg_match('/^\d+(\.\d+)?[wx]$/', $desc)) {
                continue;
            }
            $parts[] = trim($url.' '.$desc);
        }

        return $parts ? implode(', ', $parts) : null;
    }

    private function cleanStyle(string $value): ?string
    {
        $out = [];
        foreach (explode(';', $value) as $decl) {
            if (! str_contains($decl, ':')) {
                continue;
            }
            [$prop, $val] = array_map('trim', explode(':', $decl, 2));
            $prop = strtolower($prop);
            if (! in_array($prop, self::STYLE_PROPS, true) || $val === '') {
                continue;
            }
            if (preg_match('/url\s*\(|expression|javascript|@import|behavior|binding|[<>\\\\{}]/i', $val)) {
                continue;
            }
            if (! preg_match('/^[\w\s#%.,()\-\/!+]+$/u', $val)) {
                continue;
            }
            $out[] = $prop.': '.$val;
        }

        return $out ? implode('; ', $out) : null;
    }

    private function iframeAllowed(string $src): bool
    {
        if ($src === '') {
            return false;
        }
        $u = parse_url(preg_replace('#^//#', 'https://', $src) ?? $src);
        if (! $u || empty($u['host']) || ! in_array(strtolower($u['scheme'] ?? 'https'), ['http', 'https'], true)) {
            return false;
        }
        $host = strtolower($u['host']);
        $path = $u['path'] ?? '';
        if (! in_array($host, self::IFRAME_HOSTS, true)) {
            return false;
        }

        return match (true) {
            str_contains($host, 'youtube') => str_starts_with($path, '/embed/'),
            str_contains($host, 'vimeo') => str_starts_with($path, '/video/'),
            str_contains($host, 'aparat') => str_starts_with($path, '/video/video/embed/'),
            default => false,
        };
    }

    /** تبدیل لینک عادی یوتیوب/آپارات/ویمئو به آدرس embed (برای دکمه‌ی ویدیو در ویرایشگر؛ همین منطق در JS هم هست) */
    public static function embedUrl(string $url): ?string
    {
        $url = trim($url);
        if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/))([A-Za-z0-9_-]{6,15})#', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }
        if (preg_match('#aparat\.com/v/([A-Za-z0-9]+)#', $url, $m)) {
            return 'https://www.aparat.com/video/video/embed/videohash/'.$m[1].'/vt/frame';
        }
        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }
}
