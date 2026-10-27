<?php

/*
|--------------------------------------------------------------------------
| Aron Soft service catalog (single source of truth for the SERVER)
|--------------------------------------------------------------------------
| - catalog : price list used by the online estimator (ProformaController).
|             The client only sends item ids/titles; prices ALWAYS come from here.
|             Keep titles identical to resources/views/front/t-one/master.blade.php
|             (servicesData) so older cached pages keep working.
| - order   : option lists used by the /order wizard and its validation.
*/

return [

    'catalog' => [
        'wordpress' => [
            'label' => 'طراحی سایت وردپرس',
            'items' => [
                'wp_base'         => ['title' => 'طراحی سایت شرکتی (پایه)', 'price' => 12000000, 'required' => true],
                'wp_store_module' => ['title' => 'ماژول فروشگاه آنلاین (ووکامرس)', 'price' => 5000000],
                'woo_pay'         => ['title' => 'درگاه پرداخت آنلاین + نماد', 'price' => 1000000, 'parent' => 'wp_store_module'],
                'woo_compare'     => ['title' => 'سیستم مقایسه محصولات', 'price' => 1500000, 'parent' => 'wp_store_module'],
                'woo_wishlist'    => ['title' => 'لیست علاقمندی‌ها', 'price' => 800000, 'parent' => 'wp_store_module'],
                'woo_filter'      => ['title' => 'جستجو و فیلتر پیشرفته (Ajax)', 'price' => 2500000, 'parent' => 'wp_store_module'],
                'woo_stock'       => ['title' => 'سیستم انبارداری پیشرفته', 'price' => 3000000, 'parent' => 'wp_store_module'],
                'woo_api'         => ['title' => 'وب‌سرویس (API) اتصال به حسابداری', 'price' => 4000000, 'parent' => 'wp_store_module'],
                'woo_shipping'    => ['title' => 'محاسبه هزینه پست پیشرفته', 'price' => 1200000, 'parent' => 'wp_store_module'],
                'wp_content_100'  => ['title' => 'ورود اطلاعات + محتوا (۱۰۰ محصول)', 'price' => 10000000],
                'wp_entry_100'    => ['title' => 'ورود اطلاعات ساده (۱۰۰ محصول)', 'price' => 5000000],
                'wp_sms'          => ['title' => 'پنل پیامک و احراز هویت', 'price' => 2000000],
                'wp_speed'        => ['title' => 'بهینه‌سازی سرعت (راکت)', 'price' => 1500000],
            ],
        ],
        'dedicated' => [
            'label' => 'طراحی سایت اختصاصی (Laravel)',
            'items' => [
                'lar_base'         => ['title' => 'راه‌اندازی لاراول + پنل ادمین', 'price' => 25000000, 'required' => true],
                'lar_store_module' => ['title' => 'ماژول فروشگاه اختصاصی', 'price' => 15000000],
                'lar_pay'          => ['title' => 'توسعه درگاه پرداخت بانکی', 'price' => 2000000, 'parent' => 'lar_store_module'],
                'lar_compare'      => ['title' => 'ماژول مقایسه فنی محصولات', 'price' => 3000000, 'parent' => 'lar_store_module'],
                'lar_fav'          => ['title' => 'لیست علاقمندی و اطلاع‌رسانی', 'price' => 1500000, 'parent' => 'lar_store_module'],
                'lar_search'       => ['title' => 'موتور جستجوی الاستیک (Elastic)', 'price' => 6000000, 'parent' => 'lar_store_module'],
                'lar_stock'        => ['title' => 'انبارداری و مدیریت موجودی', 'price' => 5000000, 'parent' => 'lar_store_module'],
                'lar_api'          => ['title' => 'Restful API برای اپلیکیشن/خارجی', 'price' => 8000000, 'parent' => 'lar_store_module'],
                'lar_pwa'          => ['title' => 'نسخه وب‌اپلیکیشن (PWA)', 'price' => 6000000],
                'lar_chat'         => ['title' => 'چت آنلاین اختصاصی (Socket)', 'price' => 5000000],
            ],
        ],
        'devops' => [
            'label' => 'خدمات دواپس (DevOps)',
            'items' => [
                'dev_server' => ['title' => 'کانفیگ سرور لینوکس', 'price' => 3000000],
                'dev_docker' => ['title' => 'داکربندی پروژه', 'price' => 4000000],
                'dev_ci'     => ['title' => 'راه‌اندازی CI/CD', 'price' => 6000000],
            ],
        ],
        'seo' => [
            'label' => 'خدمات سئو و محتوا',
            'items' => [
                'seo_audit'   => ['title' => 'آنالیز کامل سایت', 'price' => 2000000],
                'seo_content' => ['title' => 'تولید محتوا (۱۰ مقاله)', 'price' => 5000000],
            ],
        ],
    ],

    'order' => [

        'types' => [
            'website' => [
                'label' => 'طراحی سایت',
                'icon'  => 'ph-browser',
                'hint'  => 'سایت شرکتی، معرفی خدمات، وب‌لاگ، لندینگ و پورتال',
                'features' => [
                    'corporate' => 'سایت شرکتی / معرفی',
                    'blog' => 'وب‌لاگ و مقالات',
                    'multilang' => 'چندزبانه',
                    'panel' => 'پنل مدیریت اختصاصی',
                    'seo' => 'سئو فنی و بهینه‌سازی سرعت',
                    'ticket' => 'سیستم تیکت و پشتیبانی',
                    'members' => 'ثبت‌نام و پنل کاربری',
                    'redesign' => 'بازطراحی سایت فعلی',
                ],
            ],
            'shop' => [
                'label' => 'فروشگاه اینترنتی',
                'icon'  => 'ph-shopping-cart',
                'hint'  => 'فروشگاه کالا، محصول دیجیتال یا خدمات',
                'features' => [
                    'gateway' => 'درگاه پرداخت آنلاین',
                    'inventory' => 'مدیریت موجودی و انبار',
                    'shipping' => 'محاسبه هزینه ارسال',
                    'accounting' => 'اتصال به نرم‌افزار حسابداری',
                    'discount' => 'کد تخفیف و باشگاه مشتریان',
                    'digital' => 'فروش محصول دیجیتال / دانلودی',
                    'marketplace' => 'چندفروشندگی (مارکت‌پلیس)',
                    'sms' => 'پیامک و اطلاع‌رسانی سفارش',
                ],
            ],
            'software' => [
                'label' => 'نرم‌افزار / اپلیکیشن',
                'icon'  => 'ph-device-mobile',
                'hint'  => 'سیستم سازمانی، وب‌اپ، اپلیکیشن موبایل یا API',
                'features' => [
                    'web_app' => 'وب‌اپلیکیشن',
                    'mobile' => 'اپلیکیشن موبایل (اندروید / iOS)',
                    'api' => 'طراحی و توسعه API',
                    'dashboard' => 'داشبورد و گزارش‌گیری',
                    'roles' => 'سطوح دسترسی و نقش‌ها',
                    'integration' => 'اتصال به سرویس‌های دیگر',
                    'saas' => 'معماری SaaS / چند مستأجره',
                    'maintenance' => 'پشتیبانی و نگهداری پس از تحویل',
                ],
            ],
            'seo' => [
                'label' => 'سئو و محتوا',
                'icon'  => 'ph-magnifying-glass',
                'hint'  => 'بهبود رتبه، آنالیز فنی و تولید محتوا',
                'features' => [
                    'audit' => 'آنالیز و ممیزی فنی سایت',
                    'onpage' => 'سئو داخلی (On-page)',
                    'content' => 'تولید محتوا',
                    'keyword' => 'تحقیق کلمات کلیدی',
                    'speed' => 'بهینه‌سازی سرعت',
                    'local' => 'سئو محلی',
                    'report' => 'گزارش ماهانه',
                ],
            ],
            'devops' => [
                'label' => 'DevOps و زیرساخت',
                'icon'  => 'ph-cloud-arrow-up',
                'hint'  => 'سرور، داکر، CI/CD، مانیتورینگ و امنیت',
                'features' => [
                    'server' => 'راه‌اندازی و امن‌سازی سرور',
                    'docker' => 'داکربندی',
                    'ci' => 'CI/CD',
                    'monitoring' => 'مانیتورینگ و هشدار',
                    'backup' => 'پشتیبان‌گیری خودکار',
                    'migration' => 'انتقال هاست / سرور',
                    'support' => 'پشتیبانی ۲۴/۷',
                ],
            ],
            'consult' => [
                'label' => 'مشاوره رایگان',
                'icon'  => 'ph-chats-circle',
                'hint'  => 'هنوز مطمئن نیستید؟ با یک مشاور صحبت کنید',
                'features' => [
                    'idea' => 'ارزیابی ایده و دامنه پروژه',
                    'tech' => 'انتخاب فناوری مناسب',
                    'estimate' => 'برآورد هزینه و زمان',
                    'review' => 'بررسی سایت یا نرم‌افزار فعلی',
                ],
            ],
        ],

        'budgets' => [
            'lt_50'   => 'زیر ۵۰ میلیون تومان',
            '50_150'  => '۵۰ تا ۱۵۰ میلیون تومان',
            '150_300' => '۱۵۰ تا ۳۰۰ میلیون تومان',
            'gt_300'  => 'بالای ۳۰۰ میلیون تومان',
            'unknown' => 'مطمئن نیستم، نیاز به مشاوره دارم',
        ],

        'deadlines' => [
            'urgent'   => 'فوری (کمتر از ۲ هفته)',
            '1_month'  => 'تا ۱ ماه',
            '2_months' => 'تا ۲ ماه',
            '3_plus'   => 'بیش از ۳ ماه',
            'flexible' => 'انعطاف‌پذیر',
        ],

        'contact_times' => [
            'morning'   => 'صبح (۹ تا ۱۲)',
            'noon'      => 'ظهر (۱۲ تا ۱۵)',
            'afternoon' => 'عصر (۱۵ تا ۱۸)',
            'any'       => 'هر زمان',
        ],

        'statuses' => [
            'new'       => 'جدید',
            'contacted' => 'تماس گرفته شد',
            'quoted'    => 'پیش‌فاکتور ارسال شد',
            'won'       => 'قرارداد شد',
            'lost'      => 'منصرف شد',
        ],
    ],

    'contact_subjects' => [
        'مشاوره پروژه',
        'طراحی وب‌سایت',
        'طراحی اپلیکیشن موبایل',
        'DevOps / زیرساخت',
        'پشتیبانی پروژه‌های موجود',
        'همکاری تجاری / نمایندگی',
        'سایر موضوعات',
    ],

    // اطلاعات تماس (صفحه /contact، ایمیل‌ها و ...)
    'contact' => [
        'address' => 'تهران، بلوار ارتش، کوچه نصر ۲، پلاک ۸، واحد ۸',
        'phone' => '021-22451912',
        'phone_display' => '۰۲۱-۲۲۴۵۱۹۱۲',
        'email' => 'info@aron-soft.com',
        'telegram' => 'aronsoft',
        'hours' => 'شنبه تا چهارشنبه، ۰۹:۰۰ تا ۱۷:۰۰',
        'hours_note' => 'پشتیبانی پروژه‌های فعال: ۲۴ ساعته',
        // مختصات نقشه (OpenStreetMap). فعلاً تقریبی/جایگزین است؛ با مختصات دقیق دفتر عوض شود.
        'map' => ['lat' => 35.7219, 'lng' => 51.4570, 'zoom' => 15],
    ],
];
