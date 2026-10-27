<?php

/*
 | قالب «ink» — ابزار سازمانی، روشن و جوهری (طرح هوماواچ).
 | بخش‌ها: hero (هدر + تابلوی شیفت)، rules (کارت قوانین)، shifts (برنامه‌ی هفتگی)،
 | reports (جدول گزارش)، plans (جدول مقایسه)، faq، cta (فرم سرنخ).
 */

$hint_nav = 'متن این بخش در منوی بالای صفحه؛ خالی = در منو نمایش داده نشود.';

return [
    'label' => 'ابزار سازمانی – روشن و جوهری',
    'description' => 'مناسب نرم‌افزارهای سازمانی و SaaS (مثل تردد، حسابداری، CRM). زمینه‌ی روشن خاکستری‌آبی با رنگ جوهری و یک رنگ تأکید؛ هیرو دو ستونی با تابلوی شیفت (یا تصویر محصول)، کارت‌های قانون، جدول برنامه‌ی هفتگی، جدول گزارش و جدول مقایسه‌ی نسخه‌ها.',

    'palette' => [
        ['key' => 'accent', 'label' => 'رنگ اصلی (تیک‌ها، نوار حضور، دکمه‌ی جدول)', 'default' => '#0B8F9C'],
        ['key' => 'ink', 'label' => 'رنگ جوهری (متن، دکمه‌ها، بخش تیره)', 'default' => '#0C1A26'],
        ['key' => 'soft', 'label' => 'رنگ روشن زمینه‌ی نشانه‌ها', 'default' => '#D4EEF1'],
        ['key' => 'bright', 'label' => 'رنگ درخشان (آیکن لوگو و دکمه‌ی فرم)', 'default' => '#3FC8D6'],
    ],

    'sections' => [

        'hero' => [
            'label' => 'هیرو (سربرگ و تابلوی شیفت)',
            'icon' => 'rocket-launch',
            'hint' => 'سربرگ محصول، عنوان اصلی، دکمه‌ها و تصویر سمت چپ (تابلوی شیفت یا تصویر محصول).',
            'locked' => true,
            'default_enabled' => true,
            'always' => true,
            'fields' => [
                ['key' => 'title', 'type' => 'textarea', 'label' => 'عنوان اصلی', 'hint' => 'هر خط در صفحه در یک سطر جدا می‌آید.', 'max' => 160, 'rows' => 2, 'required' => true],
                ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'توضیح زیر عنوان', 'max' => 400, 'rows' => 3],
                ['key' => 'primary_label', 'type' => 'text', 'label' => 'متن دکمه‌ی اصلی (و دکمه‌ی سربرگ)', 'max' => 40],
                ['key' => 'primary_url', 'type' => 'url', 'label' => 'پیوند دکمه‌ی اصلی', 'hint' => 'خالی = پیوند اصلی محصول.'],
                ['key' => 'secondary_label', 'type' => 'text', 'label' => 'متن دکمه‌ی دوم', 'max' => 40],
                ['key' => 'secondary_url', 'type' => 'url', 'label' => 'پیوند دکمه‌ی دوم'],
                ['key' => 'bullets', 'type' => 'lines', 'label' => 'نکته‌های کوتاه زیر دکمه‌ها', 'hint' => 'هر خط یک مورد با تیک.', 'max_items' => 4, 'max' => 80],

                ['key' => 'visual', 'type' => 'select', 'label' => 'تصویر سمت چپ', 'options' => ['builtin' => 'تابلوی شیفت (داخلی)', 'image' => 'تصویر محصول'], 'default' => 'builtin'],
                ['key' => 'image', 'type' => 'image', 'label' => 'تصویر محصول', 'hint' => 'فقط وقتی «تصویر محصول» انتخاب شده نمایش داده می‌شود.'],
                ['key' => 'frame', 'type' => 'select', 'label' => 'قاب تصویر', 'options' => ['phone' => 'گوشی', 'browser' => 'مرورگر', 'plain' => 'ساده'], 'default' => 'browser'],

                ['key' => 'board_date', 'type' => 'text', 'label' => 'تابلو ← تاریخ', 'max' => 40],
                ['key' => 'board_sub', 'type' => 'text', 'label' => 'تابلو ← زیرعنوان', 'max' => 60],
                ['key' => 'board_tabs', 'type' => 'lines', 'label' => 'تابلو ← زبانه‌ها', 'hint' => 'خط اول فعال است.', 'max_items' => 3, 'max' => 16],
                ['key' => 'board_times', 'type' => 'lines', 'label' => 'تابلو ← ساعت‌های محور زمان', 'hint' => 'معمولاً ۵ ساعت، از راست به چپ.', 'max_items' => 7, 'max' => 10],
                ['key' => 'board_now_label', 'type' => 'text', 'label' => 'تابلو ← برچسب خط «الان»', 'max' => 12],
                ['key' => 'board_now_pos', 'type' => 'text', 'label' => 'تابلو ← جای خط «الان» (درصد از راست، ۰ تا ۱۰۰)', 'max' => 6],
                [
                    'key' => 'board_rows', 'type' => 'repeater', 'label' => 'تابلو ← ردیف کارکنان', 'max_items' => 8, 'item_label' => 'name',
                    'fields' => [
                        ['key' => 'name', 'type' => 'text', 'label' => 'نام', 'max' => 40],
                        ['key' => 'role', 'type' => 'text', 'label' => 'واحد / نقش', 'max' => 30],
                        ['key' => 'tone', 'type' => 'select', 'label' => 'رنگ آواتار', 'options' => ['teal' => 'سبزآبی', 'indigo' => 'نیلی', 'amber' => 'کهربایی'], 'default' => 'teal'],
                        ['key' => 'start', 'type' => 'text', 'label' => 'شروع شیفت (درصد از راست)', 'max' => 6],
                        ['key' => 'len', 'type' => 'text', 'label' => 'طول شیفت (درصد)', 'max' => 6],
                        ['key' => 'present', 'type' => 'text', 'label' => 'طول حضور ثبت‌شده (درصد؛ ۰ = ثبت نشده)', 'max' => 6],
                        ['key' => 'late', 'type' => 'text', 'label' => 'طول بخش تأخیر (درصد؛ خالی = بدون تأخیر)', 'max' => 6],
                        ['key' => 'late_label', 'type' => 'text', 'label' => 'برچسب تأخیر', 'max' => 30],
                        ['key' => 'state', 'type' => 'text', 'label' => 'برچسب وضعیت (وقتی حضور ثبت نشده)', 'max' => 30],
                    ],
                ],
                ['key' => 'board_legend', 'type' => 'lines', 'label' => 'تابلو ← راهنما', 'hint' => 'سه خط: برنامه‌ریزی‌شده، حضور، تأخیر.', 'max_items' => 3, 'max' => 40],
                ['key' => 'toast_title', 'type' => 'text', 'label' => 'اعلان شناور ← عنوان', 'hint' => 'خالی = اعلان نمایش داده نشود.', 'max' => 60],
                ['key' => 'toast_text', 'type' => 'text', 'label' => 'اعلان شناور ← متن', 'max' => 120],
            ],
        ],

        'rules' => [
            'label' => 'قوانین (کارت‌های قانون)',
            'icon' => 'sliders',
            'hint' => 'متن سمت راست و کارت‌های قانون. هر قانون: متن + نشانه‌های رنگی + اقدام‌ها.',
            'default_enabled' => true,
            'fields' => [
                ['key' => 'nav', 'type' => 'text', 'label' => 'برچسب منو', 'hint' => $hint_nav, 'max' => 30],
                ['key' => 'title', 'type' => 'textarea', 'label' => 'عنوان', 'max' => 160, 'rows' => 2],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 500, 'rows' => 3],
                [
                    'key' => 'rules', 'type' => 'repeater', 'label' => 'قانون‌ها', 'max_items' => 8, 'item_label' => 'violet',
                    'fields' => [
                        ['key' => 'before', 'type' => 'text', 'label' => 'متن ابتدا (مثل «اگر ورود»)', 'max' => 80],
                        ['key' => 'neutral', 'type' => 'text', 'label' => 'نشانه‌ی خنثی (سفید)', 'max' => 50],
                        ['key' => 'mid', 'type' => 'text', 'label' => 'متن میانی (مثل «بیش از»)', 'max' => 60],
                        ['key' => 'violet', 'type' => 'text', 'label' => 'نشانه‌ی رنگی (مقدار)', 'max' => 50],
                        ['key' => 'after', 'type' => 'text', 'label' => 'متن پایانی (مثل «باشد»)', 'max' => 60],
                        ['key' => 'actions', 'type' => 'lines', 'label' => 'اقدام‌ها (نشانه‌های تیره)', 'hint' => 'هر خط یک اقدام.', 'max_items' => 4, 'max' => 50],
                        ['key' => 'on', 'type' => 'toggle', 'label' => 'کلید قانون روشن باشد', 'default' => true],
                    ],
                ],
                ['key' => 'add_label', 'type' => 'text', 'label' => 'متن کادر «افزودن»', 'hint' => 'خالی = نمایش داده نشود.', 'max' => 50],
            ],
        ],

        'shifts' => [
            'label' => 'شیفت‌ها (برنامه‌ی هفتگی)',
            'icon' => 'calendar-check',
            'hint' => 'جدول برنامه‌ی هفتگی. رنگ هر خانه از روی ترتیب راهنما تعیین می‌شود: اول=صبح، دوم=عصر، سوم=شب؛ بقیه نقطه‌چین.',
            'default_enabled' => true,
            'fields' => [
                ['key' => 'nav', 'type' => 'text', 'label' => 'برچسب منو', 'hint' => $hint_nav, 'max' => 30],
                ['key' => 'title', 'type' => 'textarea', 'label' => 'عنوان', 'max' => 160, 'rows' => 2],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 500, 'rows' => 3],
                ['key' => 'card_title', 'type' => 'text', 'label' => 'عنوان کارت جدول', 'max' => 60],
                ['key' => 'legend', 'type' => 'lines', 'label' => 'راهنمای رنگ‌ها', 'hint' => 'سه خط به ترتیب: شیفت اول (آبی)، دوم (نیلی)، سوم (کهربایی).', 'max_items' => 3, 'max' => 20],
                ['key' => 'days', 'type' => 'lines', 'label' => 'نام روزهای هفته', 'hint' => 'تا ۷ خط.', 'max_items' => 7, 'max' => 20],
                [
                    'key' => 'rows', 'type' => 'repeater', 'label' => 'ردیف‌های کارکنان', 'max_items' => 10, 'item_label' => 'name',
                    'fields' => [
                        ['key' => 'name', 'type' => 'text', 'label' => 'نام', 'max' => 40],
                        ['key' => 'cells', 'type' => 'lines', 'label' => 'خانه‌ی هر روز', 'hint' => 'هر خط یک روز؛ مثل «صبح»، «عصر»، «شب» یا «آزاد».', 'max_items' => 7, 'max' => 20],
                    ],
                ],
            ],
        ],

        'reports' => [
            'label' => 'گزارش (جدول ماهانه)',
            'icon' => 'chart-bar',
            'hint' => 'جدول گزارش. خانه‌ای که با «!» شروع شود به‌صورت نشان کهربایی نمایش داده می‌شود.',
            'default_enabled' => true,
            'fields' => [
                ['key' => 'nav', 'type' => 'text', 'label' => 'برچسب منو', 'hint' => $hint_nav, 'max' => 30],
                ['key' => 'title', 'type' => 'textarea', 'label' => 'عنوان', 'max' => 160, 'rows' => 2],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 500, 'rows' => 3],
                ['key' => 'columns', 'type' => 'lines', 'label' => 'عنوان ستون‌ها', 'hint' => 'ستون اول برای نام است.', 'max_items' => 7, 'max' => 30],
                [
                    'key' => 'rows', 'type' => 'repeater', 'label' => 'ردیف‌ها', 'max_items' => 12, 'item_label' => 'name',
                    'fields' => [
                        ['key' => 'name', 'type' => 'text', 'label' => 'ستون اول (نام، پررنگ)', 'max' => 40],
                        ['key' => 'cells', 'type' => 'lines', 'label' => 'خانه‌های بعدی', 'hint' => 'هر خط یک ستون؛ با «!» شروع شود = نشان کهربایی.', 'max_items' => 6, 'max' => 40],
                    ],
                ],
            ],
        ],

        'plans' => [
            'label' => 'نسخه‌ها (جدول مقایسه)',
            'icon' => 'scales',
            'hint' => 'جدول مقایسه‌ی دو یا سه نسخه/روش. هر ستون یک نسخه است؛ هر ردیف یک ویژگی با یک خط برای هر ستون.',
            'default_enabled' => true,
            'fields' => [
                ['key' => 'nav', 'type' => 'text', 'label' => 'برچسب منو', 'hint' => $hint_nav, 'max' => 30],
                ['key' => 'title', 'type' => 'textarea', 'label' => 'عنوان', 'max' => 160, 'rows' => 2],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 500, 'rows' => 3],
                [
                    'key' => 'columns', 'type' => 'repeater', 'label' => 'ستون‌ها (نسخه‌ها)', 'max_items' => 4, 'item_label' => 'title',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 40],
                        ['key' => 'sub', 'type' => 'text', 'label' => 'زیرعنوان', 'max' => 60],
                        ['key' => 'cta_label', 'type' => 'text', 'label' => 'متن دکمه‌ی ردیف آخر', 'max' => 40],
                        ['key' => 'cta_url', 'type' => 'url', 'label' => 'پیوند دکمه', 'hint' => 'خالی = پیوند اصلی محصول.'],
                        ['key' => 'cta_style', 'type' => 'select', 'label' => 'سبک دکمه', 'options' => ['solid' => 'پر', 'outline' => 'خطی'], 'default' => 'solid'],
                    ],
                ],
                [
                    'key' => 'rows', 'type' => 'repeater', 'label' => 'ردیف‌های ویژگی', 'max_items' => 12, 'item_label' => 'label',
                    'fields' => [
                        ['key' => 'label', 'type' => 'text', 'label' => 'نام ویژگی', 'max' => 40],
                        ['key' => 'values', 'type' => 'lines', 'label' => 'مقدار هر ستون', 'hint' => 'هر خط یک ستون، به همان ترتیب ستون‌ها.', 'max_items' => 4, 'max' => 120],
                    ],
                ],
                ['key' => 'cta_row_label', 'type' => 'text', 'label' => 'برچسب ردیف دکمه‌ها', 'hint' => 'مثل «شروع».', 'max' => 30],
            ],
        ],

        'faq' => [
            'label' => 'سؤالات متداول',
            'icon' => 'chat-circle-dots',
            'hint' => 'پرسش و پاسخ‌های بازشونده.',
            'default_enabled' => true,
            'fields' => [
                ['key' => 'nav', 'type' => 'text', 'label' => 'برچسب منو', 'hint' => $hint_nav, 'max' => 30],
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 100],
                [
                    'key' => 'items', 'type' => 'repeater', 'label' => 'پرسش‌ها', 'max_items' => 12, 'item_label' => 'q',
                    'fields' => [
                        ['key' => 'q', 'type' => 'text', 'label' => 'پرسش', 'max' => 200],
                        ['key' => 'a', 'type' => 'textarea', 'label' => 'پاسخ', 'max' => 1000, 'rows' => 4],
                    ],
                ],
            ],
        ],

        'cta' => [
            'label' => 'دعوت به اقدام (فرم تماس)',
            'icon' => 'phone',
            'hint' => 'کادر تیره‌ی پایانی با فرم شماره‌ی موبایل.',
            'locked' => true,
            'default_enabled' => true,
            'always' => true,
            'fields' => [
                ['key' => 'title', 'type' => 'textarea', 'label' => 'عنوان', 'max' => 160, 'rows' => 2],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 400, 'rows' => 3],
                ['key' => 'lead_form', 'type' => 'toggle', 'label' => 'فرم شماره‌ی موبایل نمایش داده شود', 'default' => true],
                ['key' => 'input_label', 'type' => 'text', 'label' => 'برچسب (مخفی) فیلد موبایل', 'max' => 40],
                ['key' => 'placeholder', 'type' => 'text', 'label' => 'متن راهنمای فیلد موبایل', 'max' => 30],
                ['key' => 'primary_label', 'type' => 'text', 'label' => 'متن دکمه', 'max' => 40],
                ['key' => 'primary_url', 'type' => 'url', 'label' => 'پیوند دکمه (وقتی فرم خاموش است)', 'hint' => 'خالی = پیوند اصلی محصول.'],
            ],
        ],
    ],

    'sample' => [
        'hero' => [
            'title' => "شیفت را بچینید.\nتردد خودش حساب می‌شود.",
            'subtitle' => 'هوماواچ ورود و خروج کارکنان را ثبت می‌کند و قوانین کار سازمان شما را خودکار اعمال می‌کند، تا منابع انسانی وقتش را صرف ورود دستی اطلاعات نکند.',
            'primary_label' => 'شروع آزمایش ۱۴ روزه',
            'primary_url' => '',
            'secondary_label' => 'درخواست مشاوره',
            'secondary_url' => '#sec-plans',
            'bullets' => ['برای شروع پرداختی لازم نیست', 'ثبت تردد با اپلیکیشن اندروید'],
            'visual' => 'builtin',
            'image' => '',
            'frame' => 'browser',
            'board_date' => 'شنبه ۲۳ مهر',
            'board_sub' => 'شعبه‌ی مرکزی، ساعت ۱۵:۳۰',
            'board_tabs' => ['روز', 'هفته'],
            'board_times' => ['۰۶:۰۰', '۱۰:۰۰', '۱۴:۰۰', '۱۸:۰۰', '۲۲:۰۰'],
            'board_now_label' => 'الان',
            'board_now_pos' => '59.4',
            'board_rows' => [
                ['name' => 'سارا احمدی', 'role' => 'صندوق', 'tone' => 'teal', 'start' => '12.5', 'len' => '50', 'present' => '51', 'late' => '', 'late_label' => '', 'state' => ''],
                ['name' => 'علی رضایی', 'role' => 'آشپزخانه', 'tone' => 'amber', 'start' => '12.5', 'len' => '50', 'present' => '50', 'late' => '2.6', 'late_label' => 'تأخیر ۲۵ دقیقه', 'state' => ''],
                ['name' => 'مریم کریمی', 'role' => 'سالن', 'tone' => 'teal', 'start' => '37.5', 'len' => '50', 'present' => '21.9', 'late' => '', 'late_label' => '', 'state' => ''],
                ['name' => 'رضا نوری', 'role' => 'سالن', 'tone' => 'indigo', 'start' => '37.5', 'len' => '50', 'present' => '0', 'late' => '', 'late_label' => '', 'state' => 'ورود ثبت نشده'],
                ['name' => 'نگار صادقی', 'role' => 'پشتیبانی', 'tone' => 'indigo', 'start' => '50', 'len' => '50', 'present' => '9.4', 'late' => '', 'late_label' => '', 'state' => ''],
                ['name' => 'حسین موسوی', 'role' => 'انبار', 'tone' => 'teal', 'start' => '0', 'len' => '50', 'present' => '50.6', 'late' => '', 'late_label' => '', 'state' => ''],
            ],
            'board_legend' => ['شیفت برنامه‌ریزی‌شده', 'حضور ثبت‌شده', 'تأخیر'],
            'toast_title' => 'قانون تأخیر اعمال شد',
            'toast_text' => '۲۵ دقیقه تأخیر برای علی رضایی ثبت شد.',
        ],
        'rules' => [
            'nav' => 'قوانین کار',
            'title' => 'قانون‌های سازمان‌تان را یک‌بار بنویسید.',
            'text' => 'تأخیر، اضافه‌کاری، فراموشی ثبت خروج؛ هر سیاستی که سازمان شما دارد به‌صورت یک جمله‌ی ساده تعریف می‌شود و روی تمام تردد اعمال می‌شود.',
            'rules' => [
                ['before' => 'اگر ورود', 'neutral' => 'بعد از شروع شیفت', 'mid' => 'بیش از', 'violet' => '۱۰ دقیقه', 'after' => 'باشد', 'actions' => ['ثبت تأخیر', 'اطلاع به سرپرست'], 'on' => true],
                ['before' => 'اگر کار روزانه بیش از', 'neutral' => '', 'mid' => '', 'violet' => '۹ ساعت', 'after' => 'باشد', 'actions' => ['ثبت اضافه‌کاری'], 'on' => true],
                ['before' => 'اگر خروج', 'neutral' => '', 'mid' => '', 'violet' => 'ثبت نشود', 'after' => '', 'actions' => ['یادآوری به کارمند', 'نیاز به تأیید سرپرست'], 'on' => true],
            ],
            'add_label' => 'افزودن قانون جدید',
        ],
        'shifts' => [
            'nav' => 'شیفت‌ها',
            'title' => 'هفته‌ی تیم را در یک نگاه ببینید.',
            'text' => 'سرپرست تیم شیفت‌ها را روی برنامه‌ی هفتگی می‌چیند و تغییر می‌دهد. نیازی نیست منابع انسانی همان برنامه را دوباره دستی وارد کند.',
            'card_title' => 'برنامه‌ی هفته‌ی آینده',
            'legend' => ['صبح', 'عصر', 'شب'],
            'days' => ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'],
            'rows' => [
                ['name' => 'سارا احمدی', 'cells' => ['صبح', 'صبح', 'صبح', 'مرخصی', 'صبح', 'آزاد', 'آزاد']],
                ['name' => 'علی رضایی', 'cells' => ['عصر', 'عصر', 'آزاد', 'عصر', 'عصر', 'عصر', 'آزاد']],
                ['name' => 'مریم کریمی', 'cells' => ['صبح', 'آزاد', 'شب', 'شب', 'شب', 'آزاد', 'آزاد']],
                ['name' => 'رضا نوری', 'cells' => ['آزاد', 'صبح', 'صبح', 'صبح', 'آزاد', 'عصر', 'عصر']],
                ['name' => 'نگار صادقی', 'cells' => ['عصر', 'عصر', 'عصر', 'آزاد', 'آزاد', 'صبح', 'صبح']],
            ],
        ],
        'reports' => [
            'nav' => 'گزارش',
            'title' => 'پایان ماه، گزارش آماده است.',
            'text' => 'ساعت کار، تأخیر و اضافه‌کاری هر نفر بر اساس همان قوانینی که تعریف کرده‌اید محاسبه می‌شود.',
            'columns' => ['کارمند', 'روز کاری', 'ساعت کار', 'تأخیر', 'اضافه‌کاری', 'غیبت'],
            'rows' => [
                ['name' => 'سارا احمدی', 'cells' => ['۲۲', '۱۷۶ ساعت', '۰', '۴ ساعت', '۰']],
                ['name' => 'علی رضایی', 'cells' => ['۲۱', '۱۶۸ ساعت و ۳۰ دقیقه', '!۱ ساعت و ۱۰ دقیقه', '۰', '۱ روز']],
                ['name' => 'مریم کریمی', 'cells' => ['۲۲', '۱۸۰ ساعت', '۰', '۱۲ ساعت', '۰']],
                ['name' => 'رضا نوری', 'cells' => ['۲۰', '۱۶۰ ساعت', '۴۵ دقیقه', '۰', '۲ روز']],
            ],
        ],
        'plans' => [
            'nav' => 'نسخه‌ها',
            'title' => 'دو روش استفاده، یک مجموعه امکانات.',
            'text' => 'نسخه ابری و نسخه روی سرور شما از نظر امکانات تفاوتی ندارند؛ فقط محل نصب و شیوه‌ی پرداخت فرق می‌کند.',
            'columns' => [
                ['title' => 'ابری', 'sub' => 'روی زیرساخت هوماواچ', 'cta_label' => 'شروع آزمایش ۱۴ روزه', 'cta_url' => '', 'cta_style' => 'solid'],
                ['title' => 'روی سرور شما', 'sub' => 'Self-Hosted', 'cta_label' => 'درخواست مشاوره', 'cta_url' => '#lead', 'cta_style' => 'outline'],
            ],
            'rows' => [
                ['label' => 'پرداخت', 'values' => ['اشتراک ماهانه به‌ازای هر کاربر', 'لایسنس سالانه به‌همراه قرارداد پشتیبانی']],
                ['label' => 'محل نصب', 'values' => ['نیازی به سرور ندارید', 'روی زیرساخت خودتان']],
                ['label' => 'امکانات', 'values' => ['همه‌ی امکانات', 'همه‌ی امکانات، دقیقاً مثل نسخه ابری']],
            ],
            'cta_row_label' => 'شروع',
        ],
        'faq' => [
            'nav' => 'سؤالات متداول',
            'title' => 'سؤالات متداول',
            'items' => [
                ['q' => 'برای شروع باید پرداخت کنم؟', 'a' => 'خیر. آزمایش ۱۴ روزه را بدون پرداخت فعال می‌کنید.'],
                ['q' => 'تفاوت نسخه ابری و Self-Hosted چیست؟', 'a' => 'در نسخه ابری با اشتراک ماهانه به‌ازای هر کاربر روی زیرساخت ما کار می‌کنید. در Self-Hosted با لایسنس سالانه و قرارداد پشتیبانی، سامانه را روی سرور خودتان نصب می‌کنید. امکانات هر دو یکسان است.'],
                ['q' => 'هوماواچ را چه کسی ساخته است؟', 'a' => 'هوماواچ توسط تیم آرون سافت طراحی و توسعه داده شده و پشتیبانی می‌شود.'],
            ],
        ],
        'cta' => [
            'title' => 'تردد سازمان را مرتب کنید.',
            'text' => 'آزمایش ۱۴ روزه را شروع کنید، یا شماره‌تان را بگذارید تا برای مشاوره تماس بگیریم.',
            'lead_form' => true,
            'input_label' => 'شماره موبایل',
            'placeholder' => '۰۹۱۲۳۴۵۶۷۸۹',
            'primary_label' => 'درخواست تماس',
            'primary_url' => '',
        ],
        '_palette' => [],
    ],
];
