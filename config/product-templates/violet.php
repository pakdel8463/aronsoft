<?php

/*
 | قالب «بنفش براق»: هیرو گرادیانی با موبایل و داشبورد، لجر تراکنش، گزارش، کارت‌های دوزبانه،
 | مخاطبان، قدم‌ها، سؤالات متداول و CTA. مناسب محصولات مالی/داشبوردی.
 */

$tn = fn (string $k, string $l, int $max = 80, string $hint = '') => ['key' => $k, 'type' => 'text', 'label' => $l, 'max' => $max] + ($hint ? ['hint' => $hint] : []);
$nav = fn (string $hint = '') => ['key' => 'nav', 'type' => 'text', 'label' => 'عنوان در منوی بالا', 'max' => 40, 'hint' => 'اگر خالی باشد این بخش در منوی هدر نمایش داده نمی‌شود.'];
$title = fn (int $rows = 2) => ['key' => 'title', 'type' => 'text', 'label' => 'عنوان بخش', 'max' => 200];
$text = fn (string $label = 'متن توضیح', int $max = 600) => ['key' => 'text', 'type' => 'textarea', 'label' => $label, 'max' => $max, 'rows' => 3];
$kind = ['key' => 'kind', 'type' => 'select', 'label' => 'نوع', 'options' => ['in' => 'درآمد (+)', 'out' => 'هزینه (−)'], 'default' => 'in'];

return [
    'label' => 'مالی و داشبورد – بنفش براق',
    'description' => 'مناسب محصولات مالی، حسابداری و داشبوردی. هیرو گرادیانی بنفش با ماکاپ موبایل و پنل نمودار (یا تصویر دلخواه)، سپس لجر تراکنش، گزارش ماهانه، کارت‌های دوزبانه، مخاطبان، قدم‌ها، سؤالات متداول و بلوک فراخوان.',

    'palette' => [
        ['key' => 'accent', 'label' => 'رنگ اصلی (گرادیان‌ها و آیکن‌ها)', 'default' => '#5B21C9'],
        ['key' => 'deep', 'label' => 'بنفش تیره (دکمه‌ها و عنوان‌های رنگی)', 'default' => '#4C1D95'],
        ['key' => 'ink', 'label' => 'رنگ متن اصلی', 'default' => '#1A1033'],
        ['key' => 'soft', 'label' => 'پس‌زمینه‌ی ملایم', 'default' => '#F6F2FF'],
    ],

    'sections' => [

        'hero' => [
            'label' => 'هیرو', 'icon' => 'rocket-launch', 'locked' => true, 'default_enabled' => true,
            'hint' => 'هدر محصول، عنوان اصلی، دکمه‌ها و تصویر/ماکاپ سمت چپ. دکمه‌ی اول در هدر هم تکرار می‌شود.',
            'fields' => [
                ['key' => 'title', 'type' => 'textarea', 'label' => 'عنوان اصلی', 'max' => 200, 'rows' => 2, 'required' => true, 'hint' => 'می‌توانید با رفتن به خط بعد، عنوان را بشکنید.'],
                ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'متن زیر عنوان', 'max' => 600, 'rows' => 3],
                ['key' => 'primary_label', 'type' => 'text', 'label' => 'دکمه‌ی اصلی: متن', 'max' => 60],
                ['key' => 'primary_url', 'type' => 'url', 'label' => 'دکمه‌ی اصلی: آدرس', 'hint' => 'خالی = لینک اصلی محصول'],
                ['key' => 'secondary_label', 'type' => 'text', 'label' => 'دکمه‌ی دوم: متن', 'max' => 60],
                ['key' => 'secondary_url', 'type' => 'url', 'label' => 'دکمه‌ی دوم: آدرس', 'hint' => 'مثلاً #sec-ledger برای رفتن به یک بخش'],
                ['key' => 'badges', 'type' => 'lines', 'label' => 'نشان‌های زیر دکمه‌ها', 'hint' => 'هر خط یک مورد (حداکثر ۴)', 'max_items' => 4, 'max' => 60],

                ['key' => 'visual', 'type' => 'select', 'label' => 'تصویر هیرو', 'options' => ['builtin' => 'ماکاپ آماده (موبایل + نمودار)', 'image' => 'تصویر دلخواه (اسکرین‌شات)'], 'default' => 'builtin'],
                ['key' => 'frame', 'type' => 'select', 'label' => 'قاب تصویر دلخواه', 'options' => ['phone' => 'قاب موبایل', 'browser' => 'قاب مرورگر', 'plain' => 'بدون قاب'], 'default' => 'phone', 'hint' => 'فقط وقتی «تصویر دلخواه» انتخاب شده.'],
                ['key' => 'image', 'type' => 'image', 'label' => 'تصویر دلخواه', 'hint' => 'فقط وقتی «تصویر دلخواه» انتخاب شده.'],
                ['key' => 'image_alt', 'type' => 'text', 'label' => 'توضیح تصویر (alt)', 'max' => 160],

                ['key' => 'biz_label', 'type' => 'text', 'label' => 'ماکاپ موبایل: برچسب بالا', 'max' => 40],
                ['key' => 'biz_name', 'type' => 'text', 'label' => 'ماکاپ موبایل: نام کسب‌وکار', 'max' => 60],
                ['key' => 'biz_letter', 'type' => 'text', 'label' => 'ماکاپ موبایل: حرف آواتار', 'max' => 2, 'hint' => 'خالی = حرف اول نام کسب‌وکار'],
                ['key' => 'balance_label', 'type' => 'text', 'label' => 'ماکاپ موبایل: برچسب موجودی', 'max' => 40],
                ['key' => 'balance', 'type' => 'text', 'label' => 'ماکاپ موبایل: مبلغ موجودی', 'max' => 30],
                ['key' => 'balance_unit', 'type' => 'text', 'label' => 'ماکاپ موبایل: واحد پول', 'max' => 20],
                ['key' => 'income_label', 'type' => 'text', 'label' => 'ماکاپ موبایل: برچسب درآمد', 'max' => 20],
                ['key' => 'income', 'type' => 'text', 'label' => 'ماکاپ موبایل: مبلغ درآمد', 'max' => 30],
                ['key' => 'expense_label', 'type' => 'text', 'label' => 'ماکاپ موبایل: برچسب هزینه', 'max' => 20],
                ['key' => 'expense', 'type' => 'text', 'label' => 'ماکاپ موبایل: مبلغ هزینه', 'max' => 30],
                ['key' => 'recent_title', 'type' => 'text', 'label' => 'ماکاپ موبایل: عنوان فهرست', 'max' => 40],
                ['key' => 'recent_all', 'type' => 'text', 'label' => 'ماکاپ موبایل: متن «همه»', 'max' => 20],
                ['key' => 'txs', 'type' => 'repeater', 'label' => 'ماکاپ موبایل: تراکنش‌های اخیر', 'hint' => 'حداکثر ۳ مورد برای جا شدن در قاب', 'max_items' => 3, 'item_label' => 'title', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 50],
                    ['key' => 'when', 'type' => 'text', 'label' => 'زمان', 'max' => 30],
                    ['key' => 'amount', 'type' => 'text', 'label' => 'مبلغ (بدون علامت)', 'max' => 30],
                    $kind,
                ]],

                ['key' => 'chart_title', 'type' => 'text', 'label' => 'پنل نمودار: عنوان', 'max' => 80],
                ['key' => 'chart_unit', 'type' => 'text', 'label' => 'پنل نمودار: برچسب واحد', 'max' => 30],
                ['key' => 'chart_alt', 'type' => 'text', 'label' => 'پنل نمودار: توضیح برای صفحه‌خوان', 'max' => 160],
                ['key' => 'chart', 'type' => 'repeater', 'label' => 'پنل نمودار: ماه‌ها', 'hint' => 'مقدار درآمد و هزینه عددی بین ۰ تا ۱۰۰ (ارتفاع میله). حداکثر ۶ ماه.', 'max_items' => 6, 'item_label' => 'label', 'fields' => [
                    ['key' => 'label', 'type' => 'text', 'label' => 'ماه', 'max' => 20],
                    ['key' => 'income', 'type' => 'text', 'label' => 'درآمد (۰ تا ۱۰۰)', 'max' => 6],
                    ['key' => 'expense', 'type' => 'text', 'label' => 'هزینه (۰ تا ۱۰۰)', 'max' => 6],
                ]],
                ['key' => 'legend_income', 'type' => 'text', 'label' => 'پنل نمودار: راهنمای درآمد', 'max' => 20],
                ['key' => 'legend_expense', 'type' => 'text', 'label' => 'پنل نمودار: راهنمای هزینه', 'max' => 20],
            ],
        ],

        'ledger' => [
            'label' => 'لجر تراکنش', 'icon' => 'receipt', 'default_enabled' => true,
            'hint' => 'متن سمت راست و جدول نمونه‌ی تراکنش‌ها در کارت.',
            'fields' => [
                $nav(),
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'متن توضیح', 'max' => 600, 'rows' => 3],
                ['key' => 'tabs', 'type' => 'lines', 'label' => 'تب‌های بالای جدول', 'hint' => 'هر خط یک تب؛ تب اول فعال نمایش داده می‌شود (حداکثر ۴).', 'max_items' => 4, 'max' => 30],
                ['key' => 'rows', 'type' => 'repeater', 'label' => 'ردیف‌های جدول', 'max_items' => 10, 'item_label' => 'title', 'fields' => [
                    ['key' => 'date', 'type' => 'text', 'label' => 'تاریخ', 'max' => 20],
                    ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 80],
                    ['key' => 'tag', 'type' => 'text', 'label' => 'برچسب دسته', 'max' => 30],
                    ['key' => 'tag_color', 'type' => 'select', 'label' => 'رنگ نقطه‌ی برچسب', 'options' => ['green' => 'سبز', 'pink' => 'صورتی', 'violet' => 'بنفش', 'amber' => 'نارنجی'], 'default' => 'green'],
                    ['key' => 'amount', 'type' => 'text', 'label' => 'مبلغ (بدون علامت)', 'max' => 30],
                    $kind,
                ]],
            ],
        ],

        'reports' => [
            'label' => 'گزارش ماهانه', 'icon' => 'chart-pie', 'default_enabled' => true,
            'hint' => 'کارت مانده‌ی ماهانه و نوارهای هزینه بر اساس دسته.',
            'fields' => [
                $nav(),
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'متن توضیح', 'max' => 600, 'rows' => 3],
                ['key' => 'card_title', 'type' => 'text', 'label' => 'کارت مانده: عنوان', 'max' => 60],
                ['key' => 'card_period', 'type' => 'text', 'label' => 'کارت مانده: دوره', 'max' => 30],
                ['key' => 'balance', 'type' => 'text', 'label' => 'کارت مانده: مبلغ', 'max' => 30],
                ['key' => 'balance_unit', 'type' => 'text', 'label' => 'کارت مانده: واحد', 'max' => 20],
                ['key' => 'income_label', 'type' => 'text', 'label' => 'کارت مانده: برچسب درآمد', 'max' => 30],
                ['key' => 'income', 'type' => 'text', 'label' => 'کارت مانده: مبلغ درآمد', 'max' => 30],
                ['key' => 'expense_label', 'type' => 'text', 'label' => 'کارت مانده: برچسب هزینه', 'max' => 30],
                ['key' => 'expense', 'type' => 'text', 'label' => 'کارت مانده: مبلغ هزینه', 'max' => 30],
                ['key' => 'cats_title', 'type' => 'text', 'label' => 'کارت دسته‌ها: عنوان', 'max' => 80],
                ['key' => 'cats', 'type' => 'repeater', 'label' => 'کارت دسته‌ها: نوارها', 'max_items' => 8, 'item_label' => 'name', 'fields' => [
                    ['key' => 'name', 'type' => 'text', 'label' => 'نام دسته', 'max' => 40],
                    ['key' => 'amount', 'type' => 'text', 'label' => 'مبلغ', 'max' => 30],
                    ['key' => 'percent', 'type' => 'text', 'label' => 'پرشدگی نوار (۰ تا ۱۰۰)', 'max' => 6],
                ]],
            ],
        ],

        'lang' => [
            'label' => 'دو زبانه', 'icon' => 'translate', 'default_enabled' => true,
            'hint' => 'کارت‌های نمونه‌ی رابط (مثلاً فارسی و انگلیسی) کنار متن توضیح.',
            'fields' => [
                $nav(),
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'متن توضیح', 'max' => 600, 'rows' => 3],
                ['key' => 'cards', 'type' => 'repeater', 'label' => 'کارت‌ها', 'max_items' => 4, 'item_label' => 'badge', 'fields' => [
                    ['key' => 'badge', 'type' => 'text', 'label' => 'نشان زبان', 'max' => 30],
                    ['key' => 'caption', 'type' => 'text', 'label' => 'عنوان کوچک', 'max' => 60],
                    ['key' => 'value', 'type' => 'text', 'label' => 'مقدار', 'max' => 30],
                    ['key' => 'unit', 'type' => 'text', 'label' => 'واحد', 'max' => 20],
                    ['key' => 'button', 'type' => 'text', 'label' => 'متن دکمه‌ی نمایشی', 'max' => 50],
                    ['key' => 'direction', 'type' => 'select', 'label' => 'جهت متن', 'options' => ['rtl' => 'راست‌به‌چپ', 'ltr' => 'چپ‌به‌راست'], 'default' => 'rtl'],
                ]],
            ],
        ],

        'audience' => [
            'label' => 'مخاطبان', 'icon' => 'users', 'default_enabled' => true,
            'hint' => 'فهرست «محصول برای چه کسانی است».',
            'fields' => [
                $nav(),
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200],
                ['key' => 'items', 'type' => 'repeater', 'label' => 'مخاطبان', 'max_items' => 8, 'item_label' => 'name', 'fields' => [
                    ['key' => 'name', 'type' => 'text', 'label' => 'نام گروه', 'max' => 60],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 300, 'rows' => 2],
                ]],
            ],
        ],

        'steps' => [
            'label' => 'قدم‌ها', 'icon' => 'list-checks', 'default_enabled' => true,
            'hint' => 'مراحل شروع کار (شماره‌ها خودکار هستند).',
            'fields' => [
                $nav(),
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200],
                ['key' => 'items', 'type' => 'repeater', 'label' => 'قدم‌ها', 'max_items' => 6, 'item_label' => 'title', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 60],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 300, 'rows' => 2],
                ]],
            ],
        ],

        'faq' => [
            'label' => 'سؤالات متداول', 'icon' => 'chat-circle-dots', 'default_enabled' => true,
            'hint' => 'پرسش‌ها و پاسخ‌ها (در داده‌ی ساختاریافته‌ی گوگل هم استفاده می‌شود).',
            'fields' => [
                $nav(),
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200],
                ['key' => 'items', 'type' => 'repeater', 'label' => 'پرسش‌ها', 'max_items' => 20, 'item_label' => 'q', 'fields' => [
                    ['key' => 'q', 'type' => 'text', 'label' => 'پرسش', 'max' => 200],
                    ['key' => 'a', 'type' => 'textarea', 'label' => 'پاسخ', 'max' => 1500, 'rows' => 4],
                ]],
            ],
        ],

        'cta' => [
            'label' => 'فراخوان پایانی', 'icon' => 'megaphone', 'locked' => true, 'default_enabled' => true,
            'hint' => 'بلوک بنفش پایانی با دو دکمه و فرم اختیاری دریافت شماره.',
            'fields' => [
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'متن', 'max' => 500, 'rows' => 3],
                ['key' => 'primary_label', 'type' => 'text', 'label' => 'دکمه‌ی اصلی: متن', 'max' => 60],
                ['key' => 'primary_url', 'type' => 'url', 'label' => 'دکمه‌ی اصلی: آدرس', 'hint' => 'خالی = لینک اصلی محصول'],
                ['key' => 'secondary_label', 'type' => 'text', 'label' => 'دکمه‌ی دوم: متن', 'max' => 60],
                ['key' => 'secondary_url', 'type' => 'url', 'label' => 'دکمه‌ی دوم: آدرس', 'hint' => 'خالی = لینک دانلود اندروید (اگر ثبت شده) وگرنه لینک اصلی'],
                ['key' => 'lead_form', 'type' => 'toggle', 'label' => 'نمایش فرم دریافت شماره موبایل', 'default' => false],
                ['key' => 'form_title', 'type' => 'text', 'label' => 'فرم: عنوان', 'max' => 100],
                ['key' => 'form_button', 'type' => 'text', 'label' => 'فرم: متن دکمه', 'max' => 40],
            ],
        ],
    ],

    'sample' => [
        'hero' => [
            'title' => 'بدانید پولتان کجا می‌رود.',
            'subtitle' => 'مانیار درآمد و هزینه‌ی شرکت یا کسب‌وکار کوچک شما را ثبت و محاسبه می‌کند. روی وب و اندروید، به فارسی و انگلیسی.',
            'primary_label' => 'شروع با مانیار',
            'primary_url' => '',
            'secondary_label' => 'مشاهده‌ی امکانات',
            'secondary_url' => '#sec-ledger',
            'badges' => ['وب و اپلیکیشن اندروید', 'فارسی و انگلیسی', 'مناسب کسب‌وکار کوچک'],
            'visual' => 'builtin', 'frame' => 'phone', 'image' => '', 'image_alt' => '',
            'biz_label' => 'کسب‌وکار شما', 'biz_name' => 'کافه‌ی نمونه', 'biz_letter' => 'ک',
            'balance_label' => 'موجودی این ماه', 'balance' => '۱۲٬۴۸۰٬۰۰۰', 'balance_unit' => 'تومان',
            'income_label' => 'درآمد', 'income' => '۳۸٬۲۰۰٬۰۰۰',
            'expense_label' => 'هزینه', 'expense' => '۲۵٬۷۲۰٬۰۰۰',
            'recent_title' => 'تراکنش‌های اخیر', 'recent_all' => 'همه',
            'txs' => [
                ['title' => 'فروش روزانه', 'when' => 'امروز', 'amount' => '۲٬۴۰۰٬۰۰۰', 'kind' => 'in'],
                ['title' => 'خرید مواد اولیه', 'when' => 'دیروز', 'amount' => '۱٬۲۰۰٬۰۰۰', 'kind' => 'out'],
                ['title' => 'اجاره‌ی ماهانه', 'when' => '۲ مهر', 'amount' => '۸٬۰۰۰٬۰۰۰', 'kind' => 'out'],
            ],
            'chart_title' => 'درآمد و هزینه، ۶ ماه اخیر', 'chart_unit' => 'میلیون تومان',
            'chart_alt' => 'نمودار درآمد و هزینه در شش ماه اخیر',
            'chart' => [
                ['label' => 'فروردین', 'income' => '70', 'expense' => '52.5'],
                ['label' => 'اردیبهشت', 'income' => '77.5', 'expense' => '60'],
                ['label' => 'خرداد', 'income' => '65', 'expense' => '55'],
                ['label' => 'تیر', 'income' => '87.5', 'expense' => '67.5'],
                ['label' => 'مرداد', 'income' => '82.5', 'expense' => '65'],
                ['label' => 'شهریور', 'income' => '95', 'expense' => '64.4'],
            ],
            'legend_income' => 'درآمد', 'legend_expense' => 'هزینه',
        ],
        'ledger' => [
            'nav' => 'ثبت تراکنش',
            'title' => 'هر تراکنش، سر جای خودش.',
            'text' => 'درآمد و هزینه را با چند لمس ثبت کنید و با دسته‌بندی، بعداً راحت پیدا و مرورشان کنید.',
            'tabs' => ['همه', 'درآمد', 'هزینه'],
            'rows' => [
                ['date' => '۲۳ مهر', 'title' => 'فروش روزانه', 'tag' => 'فروش', 'tag_color' => 'green', 'amount' => '۲٬۴۰۰٬۰۰۰', 'kind' => 'in'],
                ['date' => '۲۲ مهر', 'title' => 'خرید مواد اولیه', 'tag' => 'مواد اولیه', 'tag_color' => 'pink', 'amount' => '۱٬۲۰۰٬۰۰۰', 'kind' => 'out'],
                ['date' => '۲۰ مهر', 'title' => 'پروژه‌ی طراحی منو', 'tag' => 'خدمات', 'tag_color' => 'violet', 'amount' => '۶٬۰۰۰٬۰۰۰', 'kind' => 'in'],
                ['date' => '۱۸ مهر', 'title' => 'قبض برق و گاز', 'tag' => 'قبوض', 'tag_color' => 'amber', 'amount' => '۴۸۰٬۰۰۰', 'kind' => 'out'],
                ['date' => '۲ مهر', 'title' => 'اجاره‌ی ماهانه', 'tag' => 'اجاره', 'tag_color' => 'pink', 'amount' => '۸٬۰۰۰٬۰۰۰', 'kind' => 'out'],
            ],
        ],
        'reports' => [
            'nav' => 'گزارش',
            'title' => 'ماه را در یک نگاه ببینید.',
            'text' => 'مانیار درآمد و هزینه را در هر دوره جمع می‌زند و نشان می‌دهد پول‌تان بیشتر در کدام دسته خرج می‌شود.',
            'card_title' => 'مانده‌ی ماهانه', 'card_period' => 'شهریور',
            'balance' => '۱۲٬۴۸۰٬۰۰۰', 'balance_unit' => 'تومان',
            'income_label' => 'درآمد', 'income' => '۳۸٬۲۰۰٬۰۰۰',
            'expense_label' => 'هزینه', 'expense' => '۲۵٬۷۲۰٬۰۰۰',
            'cats_title' => 'هزینه‌ها بر اساس دسته',
            'cats' => [
                ['name' => 'اجاره', 'amount' => '۸٬۰۰۰٬۰۰۰', 'percent' => '100'],
                ['name' => 'مواد اولیه', 'amount' => '۶٬۸۰۰٬۰۰۰', 'percent' => '85'],
                ['name' => 'حقوق', 'amount' => '۵٬۵۰۰٬۰۰۰', 'percent' => '69'],
                ['name' => 'قبوض', 'amount' => '۱٬۳۸۰٬۰۰۰', 'percent' => '18'],
            ],
        ],
        'lang' => [
            'nav' => 'دو زبانه',
            'title' => 'دو زبانه از همان ابتدا.',
            'text' => 'رابط کاربری مانیار فارسی و انگلیسی دارد؛ مناسب تیم‌های فارسی‌زبان و کسب‌وکارهایی که با شریک خارجی کار می‌کنند.',
            'cards' => [
                ['badge' => 'فارسی', 'caption' => 'درآمد این ماه', 'value' => '۳۸٬۲۰۰٬۰۰۰', 'unit' => 'تومان', 'button' => 'ثبت تراکنش جدید', 'direction' => 'rtl'],
                ['badge' => 'English', 'caption' => 'Income this month', 'value' => '38,200,000', 'unit' => 'Toman', 'button' => 'Add transaction', 'direction' => 'ltr'],
            ],
        ],
        'audience' => [
            'nav' => '',
            'title' => 'مانیار برای چه کسانی است؟',
            'items' => [
                ['name' => 'کسب‌وکارهای کوچک', 'text' => 'فروشگاه، کافه، رستوران و کسب‌وکارهای خانگی که حسابداری سنگین نمی‌خواهند.'],
                ['name' => 'شرکت‌ها', 'text' => 'تیم‌هایی که باید درآمد و هزینه را منظم ثبت کنند و در پایان ماه مرور کنند.'],
                ['name' => 'فریلنسرها', 'text' => 'کسانی که درآمد پروژه‌ای دارند و می‌خواهند هزینه‌هایشان را هم کنارش ببینند.'],
            ],
        ],
        'steps' => [
            'nav' => '',
            'title' => 'سه قدم تا کنترل دخل و خرج',
            'items' => [
                ['title' => 'ثبت‌نام', 'text' => 'یک حساب کاربری بسازید.'],
                ['title' => 'ثبت تراکنش‌ها', 'text' => 'درآمدها و هزینه‌ها را وارد کنید.'],
                ['title' => 'مرور گزارش', 'text' => 'وضعیت مالی کسب‌وکارتان را ببینید.'],
            ],
        ],
        'faq' => [
            'nav' => 'سؤالات متداول',
            'title' => 'سؤالات متداول',
            'items' => [
                ['q' => 'مانیار چیست؟', 'a' => 'مانیار ابزاری برای محاسبه و مدیریت درآمد و هزینه‌ی شرکت‌ها و کسب‌وکارهای کوچک است و روی وب و اپلیکیشن اندروید در دسترس است.'],
                ['q' => 'مانیار را چه کسی ساخته است؟', 'a' => 'مانیار توسط تیم آرون سافت طراحی و توسعه داده شده و پشتیبانی می‌شود.'],
            ],
        ],
        'cta' => [
            'title' => 'دخل و خرج را مرتب کنید.',
            'text' => 'همین امروز با مانیار شروع کنید و ببینید پول‌تان کجا می‌رود.',
            'primary_label' => 'شروع با مانیار', 'primary_url' => '',
            'secondary_label' => 'دانلود اپلیکیشن اندروید', 'secondary_url' => '',
            'lead_form' => false,
            'form_title' => 'شماره‌ی موبایل‌تان را بگذارید تا تماس بگیریم', 'form_button' => 'درخواست تماس',
        ],
        '_palette' => [],
    ],
];
