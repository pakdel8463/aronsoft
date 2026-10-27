<?php

return [

    // مدل‌هایی که باید dynamic route برایشان ساخته شود
    'models' => [
        \App\Models\LandingPage::class,
        \App\Models\Portfolio::class,
        \App\Models\Post::class,
    ],

    // فیلدهای مجاز به ترتیب اولویت
    'fields' => [
        'slug',
        'path',
        'custom_url',
        'id'
    ],
];
