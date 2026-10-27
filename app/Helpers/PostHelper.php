<?php
// app/Helpers/PostHelper.php

// 🔴 حذف شد: عبارت "use DOMDocument;" 🔴

if (!function_exists('extract_headings_from_html')) {
    function extract_headings_from_html($html_content) {
        // حذف هرگونه تگ اضافی یا اسکریپت قبل از پارس کردن
        $html_content = mb_convert_encoding($html_content, 'HTML-ENTITIES', 'UTF-8');

        // 🔴 استفاده مستقیم از DOMDocument بدون 'use' 🔴
        $dom = new DOMDocument();

        // سرکوب ارورهای HTML نامعتبر (رایج در محتوای کاربران)
        @$dom->loadHTML($html_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $headings = [];
        $counter = 1;

        $h2s = $dom->getElementsByTagName('h2');
        $h3s = $dom->getElementsByTagName('h3');
        $h4s = $dom->getElementsByTagName('h4');

        $all_elements = [];
        foreach ($h2s as $h) $all_elements[] = $h;
        foreach ($h3s as $h) $all_elements[] = $h;
        foreach ($h4s as $h) $all_elements[] = $h;

        foreach ($all_elements as $element) {
            $tag_name = $element->tagName;
            $title = trim($element->textContent);

            // تولید یک اسلاگ ساده برای URL
            $slug = \Illuminate\Support\Str::slug($title);

            // تعیین سطح Heading 🔴
            $level = 0;
            if ($tag_name === 'h2') $level = 2;
            else if ($tag_name === 'h3') $level = 3;
            else if ($tag_name === 'h4') $level = 4;

            if (!empty($title)) {
                $headings[] = [
                    'id' => $slug,
                    'title' => $title,
                    'level' => $level,
                    'number' => $counter++
                ];
            }
        }

        return $headings;
    }
}
