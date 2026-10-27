<?php

/*
 | قالب‌های لندینگ محصول. هر قالب یک طرح کامل است با اسکیمای محتوای خودش (config/product-templates/{key}.php):
 |   label, description, palette (توکن‌های رنگ), sections (فرم پنل + اعتبارسنجی), sample (محتوای نمونه)
 | ویوی هر قالب: resources/views/front/t-one/apps/templates/{key}.blade.php
 | برای افزودن قالب جدید: یک فایل config + یک ویو بسازید و کلیدش را اینجا اضافه کنید.
 */
return [
    'default' => 'ink',

    'templates' => [
        'ink'    => require __DIR__.'/product-templates/ink.php',
        'violet' => require __DIR__.'/product-templates/violet.php',
        'citrus' => require __DIR__.'/product-templates/citrus.php',
    ],
];
