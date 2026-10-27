<?php

/*
 | قالب «citrus» — لندینگ ورزش و سلامت (سبز تیره + لیمویی) با ماک‌آپ‌های داخلی (فید، برنامه‌ی امروز، رژیم، چالش).
 | ویوها: resources/views/front/t-one/apps/templates/citrus.blade.php و citrus/{section}.blade.php
 */

$text = fn (string $key, string $label, int $max = 120, string $hint = '') => ['key' => $key, 'type' => 'text', 'label' => $label, 'max' => $max] + ($hint !== '' ? ['hint' => $hint] : []);
$area = fn (string $key, string $label, int $max = 600, int $rows = 3, string $hint = '') => ['key' => $key, 'type' => 'textarea', 'label' => $label, 'max' => $max, 'rows' => $rows] + ($hint !== '' ? ['hint' => $hint] : []);
$nav = fn (string $default = '') => ['key' => 'nav', 'type' => 'text', 'label' => 'عنوان در منوی بالای صفحه', 'max' => 40, 'hint' => 'خالی = در منو نمایش داده نمی‌شود.'];
$tones = ['lime' => 'لیمویی', 'amber' => 'کهربایی', 'teal' => 'فیروزه‌ای'];
$tone = fn (string $key, string $label, string $default) => ['key' => $key, 'type' => 'select', 'label' => $label, 'options' => $tones, 'default' => $default];

return [
    'label' => 'ورزش و سلامت – لیمویی و سبز تیره',
    'description' => 'مناسب اپلیکیشن‌های ورزش، تغذیه و شبکه‌ی اجتماعی. هیرو تیره با دو گوشی (فید و برنامه‌ی امروز) یا تصویر دلخواه، سپس رژیم، چالش (بلوک لیمویی)، امکانات اجتماعی، سه قدم، سؤالات متداول و دعوت به اقدام.',

    'palette' => [
        ['key' => 'dark', 'label' => 'سبز تیره (هیرو و دعوت به اقدام)', 'default' => '#062F26'],
        ['key' => 'accent', 'label' => 'رنگ لیمویی (دکمه‌ها و بلوک چالش)', 'default' => '#B6F23C'],
        ['key' => 'ink', 'label' => 'رنگ متن و حاشیه‌ها', 'default' => '#0B2A22'],
        ['key' => 'paper', 'label' => 'پس‌زمینه‌ی صفحه', 'default' => '#F4FBEF'],
    ],

    'sections' => [

        'hero' => [
            'label' => 'هیرو', 'icon' => 'rocket-launch', 'hint' => 'بالای صفحه: هدر محصول، تیتر، دکمه‌ها و تصویر/ماک‌آپ.',
            'locked' => true, 'default_enabled' => true,
            'fields' => [
                $area('title', 'تیتر اصلی', 200, 2, 'با Enter خط جدید بسازید.'),
                $area('subtitle', 'توضیح زیر تیتر', 600, 4, 'برای سئو (توضیحات صفحه) هم استفاده می‌شود.'),
                $text('primary_label', 'متن دکمه‌ی اصلی', 60),
                ['key' => 'primary_url', 'type' => 'url', 'label' => 'پیوند دکمه‌ی اصلی', 'hint' => 'خالی = پیوند اصلی محصول (دانلود اندروید/وب‌سایت).'],
                $text('secondary_label', 'متن دکمه‌ی دوم', 60, 'خالی = دکمه نمایش داده نمی‌شود.'),
                ['key' => 'secondary_url', 'type' => 'url', 'label' => 'پیوند دکمه‌ی دوم', 'hint' => 'مثلاً #sec-diet'],
                $text('header_label', 'متن دکمه‌ی هدر', 40, 'دکمه‌ی گوشه‌ی هدر (به پیوند دکمه‌ی اصلی می‌رود). خالی = پنهان.'),
                ['key' => 'visual', 'type' => 'select', 'label' => 'نوع تصویر هیرو', 'options' => ['builtin' => 'دو گوشی داخلی (فید + برنامه‌ی امروز)', 'image' => 'تصویر دلخواه'], 'default' => 'builtin'],
                ['key' => 'image', 'type' => 'image', 'label' => 'تصویر هیرو', 'hint' => 'فقط در حالت «تصویر دلخواه».'],
                $text('image_alt', 'متن جایگزین تصویر', 160),
                ['key' => 'frame', 'type' => 'select', 'label' => 'قاب تصویر', 'options' => ['phone' => 'گوشی', 'browser' => 'مرورگر', 'plain' => 'بدون قاب'], 'default' => 'phone'],

                $text('feed_title', 'گوشی پشت: عنوان فید', 40),
                ['key' => 'posts', 'type' => 'repeater', 'label' => 'گوشی پشت: پست‌ها', 'max_items' => 3, 'item_label' => 'name', 'hint' => 'ماک‌آپ داخلی؛ نمونه‌ی پست‌های شبکه‌ی اجتماعی.', 'fields' => [
                    $text('name', 'نام', 30),
                    $text('sub', 'زیرعنوان', 60),
                    $tone('tone', 'رنگ آواتار', 'lime'),
                    ['key' => 'route', 'type' => 'toggle', 'label' => 'نمایش کارت مسیر', 'default' => false],
                    $text('stat1', 'آمار اول (زیر کارت مسیر)', 30),
                    $text('stat2', 'آمار دوم', 30),
                    $area('text', 'متن پست', 200, 2),
                ]],

                $text('today_kicker', 'گوشی جلو: برچسب بالا', 30),
                $text('today_title', 'گوشی جلو: عنوان', 40),
                $text('coins', 'گوشی جلو: نشان سکه', 30),
                $text('ring_consumed', 'حلقه‌ی کالری: مصرف‌شده', 10, 'عدد؛ مثلاً ۱۳۵۰'),
                $text('ring_goal', 'حلقه‌ی کالری: هدف', 10, 'عدد؛ مثلاً ۱۹۵۰'),
                $text('ring_unit', 'حلقه‌ی کالری: واحد', 20),
                ['key' => 'macros', 'type' => 'repeater', 'label' => 'کاشی‌های درشت‌مغذی', 'max_items' => 3, 'item_label' => 'label', 'fields' => [
                    $text('label', 'عنوان', 20),
                    $text('value', 'مقدار', 20),
                    $tone('tone', 'رنگ', 'lime'),
                ]],
                $text('meals_title', 'عنوان فهرست وعده‌ها', 40),
                ['key' => 'meals', 'type' => 'repeater', 'label' => 'وعده‌ها', 'max_items' => 3, 'item_label' => 'title', 'fields' => [
                    $text('title', 'عنوان', 30),
                    $text('sub', 'توضیح', 60),
                    $text('kcal', 'کالری', 10),
                    $tone('tone', 'رنگ', 'lime'),
                ]],
            ],
        ],

        'diet' => [
            'label' => 'رژیم هوشمند', 'icon' => 'brain', 'hint' => 'متن + دو کارت (برنامه‌ی هفتگی و شمارش کالری).',
            'default_enabled' => true,
            'fields' => [
                $nav(),
                $area('title', 'عنوان بخش', 200, 2),
                $area('text', 'توضیح', 600, 4),
                $text('badge', 'کارت تیره: نشان', 40),
                $text('plan_title', 'کارت تیره: عنوان', 60),
                $area('plan_text', 'کارت تیره: متن', 300, 3),
                $text('plan_days', 'کارت تیره: تعداد روزهای فعال هفته', 2, 'عددی از ۰ تا ۷'),
                $text('cal_title', 'کارت زرد: عنوان', 60),
                $area('cal_text', 'کارت زرد: متن', 300, 3),
                $text('protein_label', 'نوار پیشرفت: عنوان', 30),
                $text('protein_value', 'نوار پیشرفت: مقدار', 30),
                $text('protein_percent', 'نوار پیشرفت: درصد پر شدن', 3, 'عددی از ۰ تا ۱۰۰'),
            ],
        ],

        'challenge' => [
            'label' => 'چالش‌ها', 'icon' => 'trophy', 'hint' => 'بلوک لیمویی با کارت چالش و رتبه‌بندی.',
            'default_enabled' => true,
            'fields' => [
                $nav(),
                $area('title', 'عنوان بخش', 200, 2),
                $area('text', 'توضیح', 600, 4),
                $text('card_title', 'کارت: عنوان چالش', 60),
                $text('badge', 'کارت: نشان (مثلاً روز ۱۲ از ۳۰)', 30),
                $text('percent', 'درصد پیشرفت', 3, 'عددی از ۰ تا ۱۰۰'),
                ['key' => 'ranking', 'type' => 'repeater', 'label' => 'رتبه‌بندی', 'max_items' => 5, 'item_label' => 'name', 'fields' => [
                    $text('name', 'نام', 30),
                    $text('stat', 'آمار', 40),
                    $tone('tone', 'رنگ آواتار', 'lime'),
                ]],
            ],
        ],

        'social' => [
            'label' => 'جامعه و امکانات', 'icon' => 'users-three', 'hint' => 'عنوان + کارت‌های امکانات.',
            'default_enabled' => true,
            'fields' => [
                $nav(),
                $area('title', 'عنوان بخش', 200, 2),
                ['key' => 'items', 'type' => 'repeater', 'label' => 'کارت‌ها', 'max_items' => 6, 'item_label' => 'title', 'fields' => [
                    ['key' => 'icon', 'type' => 'icon', 'label' => 'آیکن'],
                    $text('title', 'عنوان', 60),
                    $area('text', 'متن', 300, 3),
                    $tone('tone', 'رنگ کارت', 'teal'),
                ]],
            ],
        ],

        'steps' => [
            'label' => 'قدم‌ها', 'icon' => 'list-checks', 'hint' => 'مراحل شروع کار (شماره‌ها خودکار).',
            'default_enabled' => true,
            'fields' => [
                $nav(),
                $text('title', 'عنوان بخش', 120),
                ['key' => 'items', 'type' => 'repeater', 'label' => 'قدم‌ها', 'max_items' => 6, 'item_label' => 'title', 'fields' => [
                    $text('title', 'عنوان', 80),
                    $area('text', 'متن', 300, 3),
                ]],
            ],
        ],

        'faq' => [
            'label' => 'سؤالات متداول', 'icon' => 'chat-circle-dots', 'hint' => 'پرسش و پاسخ آکاردئونی.',
            'default_enabled' => true,
            'fields' => [
                $nav(),
                $text('title', 'عنوان بخش', 120),
                ['key' => 'items', 'type' => 'repeater', 'label' => 'پرسش‌ها', 'max_items' => 12, 'item_label' => 'q', 'fields' => [
                    $text('q', 'پرسش', 200),
                    $area('a', 'پاسخ', 1000, 4),
                ]],
            ],
        ],

        'cta' => [
            'label' => 'دعوت به اقدام', 'icon' => 'paper-plane-right', 'hint' => 'بلوک تیره‌ی پایانی با دو دکمه و فرم سرنخ اختیاری.',
            'locked' => true, 'default_enabled' => true,
            'fields' => [
                $area('title', 'عنوان', 200, 2),
                $area('text', 'متن', 400, 3),
                $text('primary_label', 'متن دکمه‌ی اصلی', 60),
                ['key' => 'primary_url', 'type' => 'url', 'label' => 'پیوند دکمه‌ی اصلی', 'hint' => 'خالی = پیوند اصلی محصول.'],
                $text('secondary_label', 'متن دکمه‌ی دوم', 60, 'خالی = دکمه نمایش داده نمی‌شود.'),
                ['key' => 'secondary_url', 'type' => 'url', 'label' => 'پیوند دکمه‌ی دوم', 'hint' => 'خالی = وب‌سایت محصول.'],
                ['key' => 'lead_form', 'type' => 'toggle', 'label' => 'نمایش فرم دریافت شماره موبایل', 'default' => false],
                $text('lead_cta', 'متن دکمه‌ی فرم', 40),
            ],
        ],
    ],

    'sample' => [
        'hero' => [
            'title' => "تناسب اندام،\nاین بار با هم.",
            'subtitle' => 'کالریتو شبکه‌ی اجتماعی ورزشی ایرانی است: رژیم و برنامه‌ی تمرینی شخصی، چالش‌های گروهی و جامعه‌ای که انگیزه‌تان را زنده نگه می‌دارد.',
            'primary_label' => 'دانلود برای اندروید',
            'primary_url' => '',
            'secondary_label' => 'امکانات را ببینید',
            'secondary_url' => '#sec-diet',
            'header_label' => 'دریافت اپلیکیشن',
            'visual' => 'builtin',
            'image' => '',
            'image_alt' => '',
            'frame' => 'phone',
            'feed_title' => 'جامعه',
            'posts' => [
                ['name' => 'سارا', 'sub' => 'دویدن ۵ کیلومتر', 'tone' => 'lime', 'route' => true, 'stat1' => '۲۴ لایک', 'stat2' => '۵ نظر', 'text' => ''],
                ['name' => 'علی', 'sub' => 'تمرین سینه و پشت‌بازو', 'tone' => 'amber', 'route' => false, 'stat1' => '', 'stat2' => '', 'text' => 'روز ۱۲ چالش، یک قدم دیگر جلوتر.'],
            ],
            'today_kicker' => 'امروز',
            'today_title' => 'برنامه‌ی من',
            'coins' => '۲۴۰ سکه',
            'ring_consumed' => '۱۳۵۰',
            'ring_goal' => '۱۹۵۰',
            'ring_unit' => 'کیلوکالری',
            'macros' => [
                ['label' => 'پروتئین', 'value' => '۸۲ گرم', 'tone' => 'lime'],
                ['label' => 'کربوهیدرات', 'value' => '۱۴۰ گرم', 'tone' => 'amber'],
                ['label' => 'چربی', 'value' => '۴۱ گرم', 'tone' => 'teal'],
            ],
            'meals_title' => 'وعده‌های امروز',
            'meals' => [
                ['title' => 'صبحانه', 'sub' => 'نان سبوس‌دار، تخم‌مرغ', 'kcal' => '۴۱۰', 'tone' => 'lime'],
                ['title' => 'ناهار', 'sub' => 'مرغ، برنج، سالاد', 'kcal' => '۷۲۰', 'tone' => 'amber'],
            ],
        ],
        'diet' => [
            'nav' => 'رژیم هوشمند',
            'title' => 'رژیمی که برای خودتان نوشته شده.',
            'text' => 'قد، وزن، هدف و سلیقه‌ی غذایی‌تان را وارد کنید تا کالریتو با کمک هوش مصنوعی یک برنامه‌ی غذایی و تمرینی پیشنهاد بدهد.',
            'badge' => 'پیشنهاد هوش مصنوعی',
            'plan_title' => 'برنامه‌ی هفتگی',
            'plan_text' => 'شنبه تا چهارشنبه تمرین، پنجشنبه استراحت فعال و جمعه‌ی آزاد.',
            'plan_days' => '5',
            'cal_title' => 'شمارش کالری',
            'cal_text' => 'وعده‌ها را ثبت کنید و مصرف روزانه‌ی کالری، پروتئین، کربوهیدرات و چربی را ببینید.',
            'protein_label' => 'پروتئین',
            'protein_value' => '۸۲ از ۱۲۰',
            'protein_percent' => '68',
        ],
        'challenge' => [
            'nav' => 'چالش‌ها',
            'title' => 'چالش بسازید، دوستانتان را دعوت کنید.',
            'text' => 'با دوستان یا همه‌ی کاربران وارد چالش شوید، پیشرفت‌تان را ثبت کنید و با سکه‌های کالریتو جایزه بگیرید.',
            'card_title' => 'چالش ۳۰ روز حرکت',
            'badge' => 'روز ۱۲ از ۳۰',
            'percent' => '40',
            'ranking' => [
                ['name' => 'سارا', 'stat' => '۱۲ روز پیاپی', 'tone' => 'lime'],
                ['name' => 'علی', 'stat' => '۱۱ روز', 'tone' => 'amber'],
                ['name' => 'مریم', 'stat' => '۹ روز', 'tone' => 'teal'],
            ],
        ],
        'social' => [
            'nav' => 'جامعه',
            'title' => 'انگیزه، وقتی تنها نیستید بیشتر می‌شود.',
            'items' => [
                ['icon' => 'ph-users', 'title' => 'دنبال کنید و دنبال شوید', 'text' => 'تمرین‌ها و پیشرفت دوستانتان را ببینید و تجربه‌هایتان را به اشتراک بگذارید.', 'tone' => 'teal'],
                ['icon' => 'ph-coins', 'title' => 'سکه‌ی کالریتو', 'text' => 'با فعالیت و شرکت در چالش‌ها سکه جمع کنید و در اپلیکیشن خرج کنید.', 'tone' => 'amber'],
                ['icon' => 'ph-chart-bar', 'title' => 'پیشرفت را ببینید', 'text' => 'وزن، کالری و تمرین‌ها را ثبت کنید و روند خودتان را در نمودار دنبال کنید.', 'tone' => 'lime'],
            ],
        ],
        'steps' => [
            'nav' => '',
            'title' => 'سه قدم تا شروع',
            'items' => [
                ['title' => 'اپلیکیشن را نصب کنید', 'text' => 'کالریتو را برای اندروید دانلود کنید.'],
                ['title' => 'هدفتان را بگویید', 'text' => 'اطلاعات بدنی و هدف را وارد کنید تا برنامه ساخته شود.'],
                ['title' => 'تمرین را شروع کنید', 'text' => 'وعده‌ها و تمرین‌ها را ثبت کنید و به چالش‌ها بپیوندید.'],
            ],
        ],
        'faq' => [
            'nav' => 'سؤالات متداول',
            'title' => 'سؤالات متداول',
            'items' => [
                ['q' => 'کالریتو چیست؟', 'a' => 'کالریتو یک شبکه‌ی اجتماعی ورزشی و تغذیه است که رژیم و برنامه‌ی تمرینی شخصی، چالش‌های گروهی و جامعه‌ی کاربران را کنار هم قرار می‌دهد.'],
                ['q' => 'برنامه‌ی رژیم چطور ساخته می‌شود؟', 'a' => 'بر اساس اطلاعاتی که وارد می‌کنید، هوش مصنوعی یک برنامه‌ی پیشنهادی می‌سازد. این برنامه جایگزین مشاوره‌ی پزشک یا متخصص تغذیه نیست.'],
                ['q' => 'سکه‌ها چه کاربردی دارند؟', 'a' => 'سکه‌ها با فعالیت و شرکت در چالش‌ها به دست می‌آیند و در خود اپلیکیشن قابل استفاده‌اند.'],
            ],
        ],
        'cta' => [
            'title' => 'امروز شروع کنید.',
            'text' => 'کالریتو را نصب کنید و اولین چالش‌تان را بسازید.',
            'primary_label' => 'دانلود برای اندروید',
            'primary_url' => '',
            'secondary_label' => 'ورود به وب‌سایت',
            'secondary_url' => '',
            'lead_form' => false,
            'lead_cta' => 'درخواست تماس',
        ],
        '_palette' => [],
    ],
];
