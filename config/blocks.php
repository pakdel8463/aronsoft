<?php

/*
|--------------------------------------------------------------------------
| کتابخانه‌ی بلوک‌های صفحه‌ساز
|--------------------------------------------------------------------------
| هر نوع بلوک: category، label، icon (Phosphor)، description، fields (اسکیما)، defaults (محتوای نمونه).
| نمای سایت: resources/views/front/t-one/blocks/{type}.blade.php — داده‌ی ادغام‌شده با پیش‌فرض‌ها را
| به‌صورت $s، خود بلوک را $block و شناسه‌ی HTML را $anchor می‌گیرد.
| نوع فیلدها: text, textarea, lines (هر خط یک مورد), editor, url, image, icon, select (options), toggle,
| number (min/max), code, repeater (fields). کلید گزینه‌های select نباید عدد باشد.
| 'half' => true یعنی فیلد نصف عرض فرم را می‌گیرد.
*/

$alt = ['key' => 'alt', 'type' => 'toggle', 'label' => 'پس‌زمینه‌ی خاکستری', 'hint' => 'برای جدا کردن بصری از بلوک قبلی', 'half' => true];
$head = fn (string $title = 'عنوان') => [
    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'برچسب کوچک بالای عنوان', 'max' => 80, 'half' => true],
    ['key' => 'title', 'type' => 'text', 'label' => $title, 'max' => 200, 'half' => true],
    ['key' => 'description', 'type' => 'textarea', 'label' => 'توضیح زیر عنوان', 'max' => 600, 'rows' => 2],
];

return [

    /* ================================================================ هیرو و سربرگ */

    'hero' => [
        'category' => 'hero',
        'label' => 'هیرو (بالای صفحه)',
        'icon' => 'ph-rocket-launch',
        'description' => 'عنوان بزرگ، توضیح، دو دکمه و فهرست تیک‌دار؛ با ماکاپ داشبورد یا تصویر دلخواه.',
        'fields' => [
            ['key' => 'badge', 'type' => 'text', 'label' => 'برچسب بالای عنوان', 'max' => 80, 'half' => true],
            ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200, 'half' => true],
            ['key' => 'title_accent', 'type' => 'text', 'label' => 'بخش رنگی عنوان', 'hint' => 'اگر این عبارت داخل عنوان باشد آبی می‌شود.', 'max' => 120, 'half' => true],
            ['key' => 'visual', 'type' => 'select', 'label' => 'تصویر کنار متن', 'options' => ['mockup' => 'ماکاپ داشبورد', 'image' => 'تصویر دلخواه', 'none' => 'بدون تصویر (متن وسط‌چین)'], 'default' => 'mockup', 'half' => true],
            ['key' => 'description', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 600, 'rows' => 3],
            ['key' => 'primary_label', 'type' => 'text', 'label' => 'متن دکمه‌ی اصلی', 'max' => 60, 'half' => true],
            ['key' => 'primary_url', 'type' => 'url', 'label' => 'لینک دکمه‌ی اصلی', 'half' => true],
            ['key' => 'secondary_label', 'type' => 'text', 'label' => 'متن دکمه‌ی دوم', 'max' => 60, 'half' => true],
            ['key' => 'secondary_url', 'type' => 'url', 'label' => 'لینک دکمه‌ی دوم', 'half' => true],
            ['key' => 'checklist', 'type' => 'lines', 'label' => 'فهرست تیک‌دار زیر دکمه‌ها', 'max_items' => 5, 'max' => 60],
            ['key' => 'image', 'type' => 'image', 'label' => 'تصویر دلخواه', 'hint' => 'وقتی «تصویر کنار متن» روی «تصویر دلخواه» باشد.'],
        ],
        'defaults' => [
            'badge' => 'پذیرش پروژه جدید فعال است',
            'title' => 'مهندسیِ آینده دیجیتال برای کسب‌وکارهای پیشرو',
            'title_accent' => 'آینده دیجیتال',
            'visual' => 'mockup',
            'description' => 'ما در آرون‌سافت، ایده‌های پیچیده را به نرم‌افزارهای قدرتمند تبدیل می‌کنیم؛ از توسعه وب اختصاصی تا زیرساخت‌های DevOps مقیاس‌پذیر.',
            'primary_label' => 'ثبت سفارش پروژه',
            'primary_url' => '/order',
            'secondary_label' => 'مشاهده نمونه‌کارها',
            'secondary_url' => '/portfolios',
            'checklist' => ['مشاوره رایگان', 'پیش‌فاکتور رسمی', 'پشتیبانی پس از تحویل'],
            'image' => '',
        ],
    ],

    'page_header' => [
        'category' => 'hero',
        'label' => 'سربرگ صفحه',
        'icon' => 'ph-layout',
        'description' => 'عنوان صفحه با مسیر (breadcrumb) و توضیح کوتاه؛ برای صفحه‌های داخلی.',
        'fields' => [
            ['key' => 'eyebrow', 'type' => 'text', 'label' => 'برچسب بالای عنوان', 'max' => 80, 'half' => true],
            ['key' => 'title', 'type' => 'text', 'label' => 'عنوان (H1)', 'hint' => 'خالی = عنوان صفحه', 'max' => 200, 'half' => true],
            ['key' => 'lead', 'type' => 'textarea', 'label' => 'توضیح کوتاه', 'max' => 400, 'rows' => 2],
            ['key' => 'breadcrumb', 'type' => 'toggle', 'label' => 'نمایش مسیر صفحه', 'default' => true, 'half' => true],
        ],
        'defaults' => ['eyebrow' => '', 'title' => '', 'lead' => '', 'breadcrumb' => true],
    ],

    /* ================================================================ محتوا */

    'rich_text' => [
        'category' => 'content',
        'label' => 'متن (ویرایشگر کامل)',
        'icon' => 'ph-file-text',
        'description' => 'متن آزاد با تیتر، تصویر، جدول و لینک.',
        'fields' => [
            ...$head(),
            ['key' => 'body', 'type' => 'editor', 'label' => 'متن'],
            ['key' => 'width', 'type' => 'select', 'label' => 'عرض متن', 'options' => ['narrow' => 'باریک (خوانایی بهتر)', 'wide' => 'تمام‌عرض'], 'default' => 'narrow', 'half' => true],
            $alt,
        ],
        'defaults' => [
            'eyebrow' => '', 'title' => 'عنوان بخش', 'description' => '',
            'body' => '<p>این یک متن نمونه است. با ویرایشگر می‌توانید تیتر، فهرست، تصویر، جدول و لینک اضافه کنید.</p>',
            'width' => 'narrow', 'alt' => false,
        ],
    ],

    'image_text' => [
        'category' => 'content',
        'label' => 'تصویر و متن',
        'icon' => 'ph-image',
        'description' => 'دو ستون: تصویر در یک طرف، متن و فهرست و دکمه در طرف دیگر.',
        'fields' => [
            ['key' => 'eyebrow', 'type' => 'text', 'label' => 'برچسب کوچک بالای عنوان', 'max' => 80, 'half' => true],
            ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200, 'half' => true],
            ['key' => 'body', 'type' => 'editor', 'label' => 'متن'],
            ['key' => 'checklist', 'type' => 'lines', 'label' => 'فهرست تیک‌دار', 'max_items' => 8, 'max' => 120],
            ['key' => 'image', 'type' => 'image', 'label' => 'تصویر'],
            ['key' => 'image_side', 'type' => 'select', 'label' => 'جای تصویر', 'options' => ['end' => 'سمت چپ', 'start' => 'سمت راست'], 'default' => 'end', 'half' => true],
            $alt,
            ['key' => 'button_label', 'type' => 'text', 'label' => 'متن دکمه', 'max' => 60, 'half' => true],
            ['key' => 'button_url', 'type' => 'url', 'label' => 'لینک دکمه', 'half' => true],
        ],
        'defaults' => [
            'eyebrow' => 'چرا آرون سافت', 'title' => 'از ایده تا اجرا، با یک تیم',
            'body' => '<p>طراحی، توسعه، استقرار و پشتیبانی را یک تیم انجام می‌دهد؛ پس پروژه‌ی شما بین چند پیمانکار دست‌به‌دست نمی‌شود.</p>',
            'checklist' => ['قرارداد و پیش‌فاکتور رسمی', 'گزارش پیشرفت هفتگی', 'تحویل سورس‌کد کامل'],
            'image' => '', 'image_side' => 'end', 'alt' => false,
            'button_label' => 'درخواست مشاوره', 'button_url' => '/contact',
        ],
    ],

    'list_cards' => [
        'category' => 'content',
        'label' => 'متن و فهرست کارت‌ها',
        'icon' => 'ph-list-checks',
        'description' => 'متن و دکمه در یک ستون، فهرست کارت‌های کوچک با برچسب وضعیت در ستون دیگر (مثل آکادمی).',
        'fields' => [
            ...$head(),
            ['key' => 'checklist', 'type' => 'lines', 'label' => 'فهرست تیک‌دار', 'max_items' => 6, 'max' => 120],
            ['key' => 'button_label', 'type' => 'text', 'label' => 'متن دکمه', 'max' => 60, 'half' => true],
            ['key' => 'button_url', 'type' => 'url', 'label' => 'لینک دکمه', 'half' => true],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'کارت‌ها', 'item_label' => 'title', 'max_items' => 8, 'fields' => [
                ['key' => 'icon', 'type' => 'icon', 'label' => 'آیکن'],
                ['key' => 'chip', 'type' => 'text', 'label' => 'برچسب وضعیت', 'max' => 30],
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 120],
                ['key' => 'meta', 'type' => 'text', 'label' => 'توضیح کوتاه', 'max' => 160],
            ]],
            $alt,
        ],
        'defaults' => [
            'eyebrow' => 'آکادمی آرون سافت', 'title' => 'فقط پروژه نگیرید، متخصص شوید.',
            'description' => 'دوره‌های آموزشی ما دقیقاً همان مباحثی هستند که تیم فنی ما در پروژه‌های واقعی استفاده می‌کند؛ بدون حاشیه و کاملاً پروژه‌محور.',
            'checklist' => ['سرفصل‌های برگرفته از پروژه‌های واقعی', 'تمرین عملی و رفع اشکال در حین دوره', 'مناسب توسعه‌دهندگان و مدیران فنی'],
            'button_label' => 'اطلاع از زمان دوره‌ها', 'button_url' => '/contact',
            'items' => [
                ['icon' => 'ph-atom', 'chip' => 'ثبت‌نام زودهنگام', 'title' => 'دوره جامع ری‌اکت', 'meta' => '۴۰ ساعت آموزش پروژه‌محور'],
                ['icon' => 'ph-cube', 'chip' => 'تکمیل‌شده', 'title' => 'دوره جامع مسترینگ داکر', 'meta' => '۱۲ ساعت؛ از کانتینر تا Dockerfile امنیتی'],
                ['icon' => 'ph-git-branch', 'chip' => 'به‌زودی', 'title' => 'ورک‌شاپ CI/CD با GitLab', 'meta' => 'سناریوهای واقعی پایپ‌لاین‌نویسی'],
            ],
            'alt' => false,
        ],
    ],

    /* ================================================================ خدمات، ویژگی‌ها و مراحل */

    'services' => [
        'category' => 'features',
        'label' => 'کارت‌های خدمات / ویژگی‌ها',
        'icon' => 'ph-squares-four',
        'description' => 'شبکه‌ی کارت‌های آیکن‌دار با عنوان، متن و لینک.',
        'fields' => [
            ...$head(),
            ['key' => 'columns', 'type' => 'select', 'label' => 'تعداد ستون در دسکتاپ', 'options' => ['two' => '۲ ستون', 'three' => '۳ ستون', 'four' => '۴ ستون'], 'default' => 'three', 'half' => true],
            $alt,
            ['key' => 'items', 'type' => 'repeater', 'label' => 'کارت‌ها', 'item_label' => 'title', 'max_items' => 12, 'fields' => [
                ['key' => 'icon', 'type' => 'icon', 'label' => 'آیکن'],
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 120],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'متن', 'max' => 400],
                ['key' => 'url', 'type' => 'url', 'label' => 'لینک (اختیاری)'],
                ['key' => 'link_label', 'type' => 'text', 'label' => 'متن لینک', 'max' => 40],
            ]],
        ],
        'defaults' => [
            'eyebrow' => 'خدمات', 'title' => 'راهکارهای جامع برای رشد دیجیتال کسب‌وکار شما',
            'description' => 'از ایده تا اجرا و پشتیبانی، همه‌ی مراحل را یک تیم انجام می‌دهد.',
            'columns' => 'three', 'alt' => false,
            'items' => [
                ['icon' => 'ph-browser', 'title' => 'طراحی سایت', 'text' => 'سایت شرکتی، معرفی خدمات و لندینگ؛ سریع، ریسپانسیو و آماده‌ی سئو.', 'url' => '/order?type=website', 'link_label' => 'ثبت سفارش'],
                ['icon' => 'ph-shopping-cart', 'title' => 'فروشگاه اینترنتی', 'text' => 'فروشگاه کالا یا محصول دیجیتال با درگاه پرداخت، انبارداری و پنل مدیریت.', 'url' => '/order?type=shop', 'link_label' => 'ثبت سفارش'],
                ['icon' => 'ph-code', 'title' => 'توسعه نرم‌افزار', 'text' => 'سامانه‌ها و وب‌اپلیکیشن‌های اختصاصی با Laravel و Next.js برای عملکرد بالا.', 'url' => '/services/web-development', 'link_label' => 'مشاهده جزئیات'],
                ['icon' => 'ph-globe', 'title' => 'وردپرس پیشرفته', 'text' => 'طراحی قالب اختصاصی، پلاگین‌نویسی و بهینه‌سازی حرفه‌ای ووکامرس.', 'url' => '/services/wordpress-website-design', 'link_label' => 'مشاهده جزئیات'],
                ['icon' => 'ph-hard-drives', 'title' => 'خدمات DevOps', 'text' => 'راه‌اندازی سرور، داکر، CI/CD و تامین امنیت زیرساخت‌های نرم‌افزاری شما.', 'url' => '/services/devops-services', 'link_label' => 'مشاهده جزئیات'],
                ['icon' => 'ph-magnifying-glass', 'title' => 'سئو و محتوا', 'text' => 'سئوی فنی، تحقیق کلمات کلیدی و استراتژی محتوا برای دیده‌شدن در گوگل.', 'url' => '/services/seo', 'link_label' => 'مشاهده جزئیات'],
            ],
        ],
    ],

    'steps' => [
        'category' => 'features',
        'label' => 'مراحل کار',
        'icon' => 'ph-list-checks',
        'description' => 'مراحل شماره‌دار یک فرایند (مثلاً از مشاوره تا تحویل).',
        'fields' => [
            ...$head(),
            $alt,
            ['key' => 'items', 'type' => 'repeater', 'label' => 'مراحل', 'item_label' => 'title', 'max_items' => 8, 'fields' => [
                ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 120],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 400],
            ]],
        ],
        'defaults' => [
            'eyebrow' => 'روند همکاری', 'title' => 'پروژه‌ی شما در چهار گام', 'description' => '', 'alt' => false,
            'items' => [
                ['title' => 'مشاوره و نیازسنجی', 'text' => 'نیاز شما را دقیق بررسی می‌کنیم و راهکار مناسب را پیشنهاد می‌دهیم.'],
                ['title' => 'پیش‌فاکتور و قرارداد', 'text' => 'هزینه، زمان‌بندی و خروجی هر مرحله شفاف و مکتوب می‌شود.'],
                ['title' => 'طراحی و توسعه', 'text' => 'با گزارش پیشرفت منظم، پروژه را مرحله‌به‌مرحله پیش می‌بریم.'],
                ['title' => 'تحویل و پشتیبانی', 'text' => 'پس از تست و استقرار، کنار شما می‌مانیم.'],
            ],
        ],
    ],

    'logos' => [
        'category' => 'features',
        'label' => 'نوار تکنولوژی / مشتریان',
        'icon' => 'ph-stack',
        'description' => 'ردیف برچسب‌های آیکن‌دار؛ برای تکنولوژی‌ها، مشتریان یا مزیت‌های کوتاه.',
        'fields' => [
            ['key' => 'title', 'type' => 'text', 'label' => 'متن بالای نوار', 'max' => 160],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'برچسب‌ها', 'item_label' => 'label', 'max_items' => 16, 'fields' => [
                ['key' => 'icon', 'type' => 'icon', 'label' => 'آیکن'],
                ['key' => 'label', 'type' => 'text', 'label' => 'متن', 'max' => 40],
            ]],
        ],
        'defaults' => [
            'title' => 'تکنولوژی‌هایی که روزانه با آن‌ها کار می‌کنیم',
            'items' => [
                ['icon' => 'ph-brackets-curly', 'label' => 'Laravel'], ['icon' => 'ph-lightning', 'label' => 'Next.js'],
                ['icon' => 'ph-cube', 'label' => 'Docker'], ['icon' => 'ph-globe', 'label' => 'WordPress'],
                ['icon' => 'ph-device-mobile', 'label' => 'React Native'], ['icon' => 'ph-terminal-window', 'label' => 'Linux'],
                ['icon' => 'ph-database', 'label' => 'MySQL'],
            ],
        ],
    ],

    /* ================================================================ اعتماد، آمار و نظرات */

    'stats' => [
        'category' => 'social',
        'label' => 'آمار و ارقام',
        'icon' => 'ph-chart-bar',
        'description' => 'چند عدد بزرگ با برچسب (مثلاً تعداد پروژه و سال تجربه).',
        'fields' => [
            ['key' => 'eyebrow', 'type' => 'text', 'label' => 'برچسب کوچک', 'max' => 80, 'half' => true],
            ['key' => 'title', 'type' => 'text', 'label' => 'عنوان (اختیاری)', 'max' => 200, 'half' => true],
            ['key' => 'style', 'type' => 'select', 'label' => 'ظاهر', 'options' => ['light' => 'روشن', 'dark' => 'تیره'], 'default' => 'light', 'half' => true],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'آمار', 'item_label' => 'label', 'max_items' => 6, 'fields' => [
                ['key' => 'value', 'type' => 'text', 'label' => 'عدد', 'max' => 20],
                ['key' => 'label', 'type' => 'text', 'label' => 'برچسب', 'max' => 60],
                ['key' => 'icon', 'type' => 'icon', 'label' => 'آیکن (اختیاری)'],
            ]],
        ],
        'defaults' => [
            'eyebrow' => '', 'title' => '', 'style' => 'light',
            'items' => [
                ['value' => '+۱۲۰', 'label' => 'پروژه‌ی تحویل‌شده', 'icon' => 'ph-briefcase'],
                ['value' => '+۸', 'label' => 'سال تجربه', 'icon' => 'ph-calendar-check'],
                ['value' => '۹۸٪', 'label' => 'رضایت مشتری', 'icon' => 'ph-smiley'],
                ['value' => '۲۴/۷', 'label' => 'پشتیبانی', 'icon' => 'ph-headset'],
            ],
        ],
    ],

    'testimonials' => [
        'category' => 'social',
        'label' => 'نظرات مشتریان',
        'icon' => 'ph-quotes',
        'description' => 'کارت‌های نقل‌قول با نام، سمت و تصویر.',
        'fields' => [
            ...$head(),
            $alt,
            ['key' => 'items', 'type' => 'repeater', 'label' => 'نظرها', 'item_label' => 'name', 'max_items' => 9, 'fields' => [
                ['key' => 'name', 'type' => 'text', 'label' => 'نام', 'max' => 80],
                ['key' => 'role', 'type' => 'text', 'label' => 'سمت / شرکت', 'max' => 120],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'متن نظر', 'max' => 600],
                ['key' => 'avatar', 'type' => 'image', 'label' => 'تصویر (اختیاری)'],
            ]],
        ],
        'defaults' => [
            'eyebrow' => 'نظر مشتریان', 'title' => 'آن‌ها به ما اعتماد کردند', 'description' => '', 'alt' => true,
            'items' => [
                ['name' => 'نام مشتری', 'role' => 'مدیرعامل شرکت نمونه', 'text' => 'تیم آرون‌سافت پروژه را دقیق و به‌موقع تحویل داد و بعد از تحویل هم پشتیبانی خوبی داشت.', 'avatar' => ''],
                ['name' => 'نام مشتری', 'role' => 'مدیر فروش فروشگاه نمونه', 'text' => 'فروشگاه اینترنتی ما سریع‌تر شد و فروش آنلاین در سه ماه دو برابر شد.', 'avatar' => ''],
                ['name' => 'نام مشتری', 'role' => 'مدیر فنی استارتاپ نمونه', 'text' => 'زیرساخت داکر و CI/CD را راه‌اندازی کردند؛ حالا انتشار نسخه‌ی جدید چند دقیقه طول می‌کشد.', 'avatar' => ''],
            ],
        ],
    ],

    'faq' => [
        'category' => 'social',
        'label' => 'پرسش‌های متداول',
        'icon' => 'ph-question',
        'description' => 'سؤال و جواب‌های بازشونده؛ با اسکیمای FAQ برای گوگل.',
        'fields' => [
            ...$head(),
            ['key' => 'schema', 'type' => 'toggle', 'label' => 'افزودن اسکیمای FAQ برای گوگل', 'default' => true, 'half' => true],
            $alt,
            ['key' => 'items', 'type' => 'repeater', 'label' => 'پرسش‌ها', 'item_label' => 'question', 'max_items' => 20, 'fields' => [
                ['key' => 'question', 'type' => 'text', 'label' => 'پرسش', 'max' => 200],
                ['key' => 'answer', 'type' => 'textarea', 'label' => 'پاسخ', 'max' => 1500, 'rows' => 3],
            ]],
        ],
        'defaults' => [
            'eyebrow' => 'سؤالات رایج', 'title' => 'پرسش‌های متداول', 'description' => '', 'schema' => true, 'alt' => false,
            'items' => [
                ['question' => 'هزینه‌ی طراحی سایت چقدر است؟', 'answer' => 'هزینه به امکانات و حجم کار بستگی دارد. بعد از نیازسنجی، پیش‌فاکتور رسمی و دقیق ارسال می‌کنیم.'],
                ['question' => 'پروژه چقدر طول می‌کشد؟', 'answer' => 'یک سایت شرکتی معمولاً بین دو تا چهار هفته و پروژه‌های اختصاصی بسته به دامنه‌ی کار زمان‌بندی می‌شوند.'],
                ['question' => 'بعد از تحویل پشتیبانی دارید؟', 'answer' => 'بله؛ پشتیبانی فنی و رفع اشکال بعد از تحویل بخشی از قرارداد است.'],
            ],
        ],
    ],

    'pricing' => [
        'category' => 'social',
        'label' => 'تعرفه‌ها',
        'icon' => 'ph-tag',
        'description' => 'کارت‌های پلن قیمت با فهرست امکانات و دکمه؛ یک پلن می‌تواند «پیشنهادی» باشد.',
        'fields' => [
            ...$head(),
            $alt,
            ['key' => 'plans', 'type' => 'repeater', 'label' => 'پلن‌ها', 'item_label' => 'name', 'max_items' => 4, 'fields' => [
                ['key' => 'name', 'type' => 'text', 'label' => 'نام پلن', 'max' => 60],
                ['key' => 'price', 'type' => 'text', 'label' => 'قیمت', 'max' => 40],
                ['key' => 'period', 'type' => 'text', 'label' => 'واحد (مثلاً «تومان / ماهانه»)', 'max' => 40],
                ['key' => 'description', 'type' => 'text', 'label' => 'توضیح کوتاه', 'max' => 160],
                ['key' => 'features', 'type' => 'lines', 'label' => 'امکانات', 'max_items' => 12, 'max' => 120],
                ['key' => 'button_label', 'type' => 'text', 'label' => 'متن دکمه', 'max' => 40],
                ['key' => 'button_url', 'type' => 'url', 'label' => 'لینک دکمه'],
                ['key' => 'featured', 'type' => 'toggle', 'label' => 'پلن پیشنهادی (برجسته)'],
            ]],
        ],
        'defaults' => [
            'eyebrow' => 'تعرفه‌ها', 'title' => 'پلن مناسب کسب‌وکارتان را انتخاب کنید', 'description' => '', 'alt' => false,
            'plans' => [
                ['name' => 'پایه', 'price' => '۱۵', 'period' => 'میلیون تومان', 'description' => 'سایت معرفی کسب‌وکار', 'features' => ['تا ۵ صفحه', 'طراحی ریسپانسیو', 'سئوی پایه'], 'button_label' => 'ثبت سفارش', 'button_url' => '/order', 'featured' => false],
                ['name' => 'حرفه‌ای', 'price' => '۳۵', 'period' => 'میلیون تومان', 'description' => 'سایت شرکتی کامل', 'features' => ['صفحات نامحدود', 'پنل مدیریت محتوا', 'بلاگ و سئوی پیشرفته', '۶ ماه پشتیبانی'], 'button_label' => 'ثبت سفارش', 'button_url' => '/order', 'featured' => true],
                ['name' => 'فروشگاهی', 'price' => '۶۰', 'period' => 'میلیون تومان', 'description' => 'فروشگاه اینترنتی', 'features' => ['درگاه پرداخت', 'مدیریت انبار', 'گزارش فروش'], 'button_label' => 'ثبت سفارش', 'button_url' => '/order?type=shop', 'featured' => false],
            ],
        ],
    ],

    /* ================================================================ محتوای پویا */

    'portfolio' => [
        'category' => 'dynamic',
        'label' => 'آخرین نمونه‌کارها',
        'icon' => 'ph-briefcase',
        'description' => 'نمونه‌کارهای منتشرشده، خودکار از بخش نمونه‌کارها.',
        'fields' => [
            ...$head(),
            ['key' => 'count', 'type' => 'select', 'label' => 'تعداد', 'options' => ['three' => '۳', 'six' => '۶', 'nine' => '۹'], 'default' => 'six', 'half' => true],
            ['key' => 'link_label', 'type' => 'text', 'label' => 'متن لینک «همه»', 'hint' => 'خالی = بدون لینک', 'max' => 60, 'half' => true],
            $alt,
        ],
        'defaults' => [
            'eyebrow' => 'نمونه‌کارها', 'title' => 'پروژه‌های منتخب', 'description' => 'ترکیبی از هنر طراحی و مهندسی نرم‌افزار.',
            'count' => 'six', 'link_label' => 'مشاهده همه پروژه‌ها', 'alt' => true,
        ],
    ],

    'posts' => [
        'category' => 'dynamic',
        'label' => 'آخرین مقالات',
        'icon' => 'ph-article',
        'description' => 'آخرین نوشته‌های منتشرشده‌ی وبلاگ.',
        'fields' => [
            ...$head(),
            ['key' => 'count', 'type' => 'select', 'label' => 'تعداد', 'options' => ['three' => '۳', 'six' => '۶'], 'default' => 'three', 'half' => true],
            ['key' => 'link_label', 'type' => 'text', 'label' => 'متن لینک «همه»', 'hint' => 'خالی = بدون لینک', 'max' => 60, 'half' => true],
            $alt,
        ],
        'defaults' => [
            'eyebrow' => 'وبلاگ', 'title' => 'آخرین مقالات', 'description' => '',
            'count' => 'three', 'link_label' => 'همه مقالات', 'alt' => true,
        ],
    ],

    /* ================================================================ فراخوان */

    'cta_lead' => [
        'category' => 'cta',
        'label' => 'فراخوان با فرم مشاوره',
        'icon' => 'ph-handshake',
        'description' => 'کادر تیره با دو دکمه و فرم کوتاه درخواست تماس (لیدها در پنل ثبت می‌شوند).',
        'fields' => [
            ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200],
            ['key' => 'description', 'type' => 'textarea', 'label' => 'توضیح', 'max' => 600],
            ['key' => 'primary_label', 'type' => 'text', 'label' => 'متن دکمه‌ی اصلی', 'max' => 60, 'half' => true],
            ['key' => 'primary_url', 'type' => 'url', 'label' => 'لینک دکمه‌ی اصلی', 'half' => true],
            ['key' => 'secondary_label', 'type' => 'text', 'label' => 'متن دکمه‌ی دوم', 'max' => 60, 'half' => true],
            ['key' => 'secondary_url', 'type' => 'url', 'label' => 'لینک دکمه‌ی دوم', 'half' => true],
            ['key' => 'form_title', 'type' => 'text', 'label' => 'عنوان فرم', 'max' => 120, 'half' => true],
            ['key' => 'form_button', 'type' => 'text', 'label' => 'متن دکمه‌ی فرم', 'max' => 40, 'half' => true],
            ['key' => 'form_text', 'type' => 'text', 'label' => 'توضیح فرم', 'max' => 200],
            ['key' => 'request_type', 'type' => 'text', 'label' => 'نوع درخواست (در پنل لیدها)', 'max' => 120, 'half' => true],
            ['key' => 'with_name', 'type' => 'toggle', 'label' => 'فیلد نام هم پرسیده شود', 'half' => true],
        ],
        'defaults' => [
            'title' => 'آماده شروع هستید؟',
            'description' => 'پروژه‌تان را ثبت کنید تا پیش‌فاکتور و زمان‌بندی اولیه را برایتان بفرستیم، یا فقط شماره‌تان را بگذارید تا کارشناس ما تماس بگیرد.',
            'primary_label' => 'ثبت سفارش پروژه', 'primary_url' => '/order',
            'secondary_label' => 'تماس با ما', 'secondary_url' => '/contact',
            'form_title' => 'درخواست مشاوره رایگان', 'form_button' => 'درخواست مشاوره',
            'form_text' => 'شماره موبایل را وارد کنید؛ در اولین فرصت تماس می‌گیریم.',
            'request_type' => 'مشاوره رایگان (صفحه‌ساز)', 'with_name' => false,
        ],
    ],

    'cta_band' => [
        'category' => 'cta',
        'label' => 'نوار فراخوان',
        'icon' => 'ph-megaphone',
        'description' => 'کادر تیره‌ی وسط‌چین با عنوان، متن و دو دکمه.',
        'fields' => [
            ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 200],
            ['key' => 'text', 'type' => 'textarea', 'label' => 'متن', 'max' => 600],
            ['key' => 'primary_label', 'type' => 'text', 'label' => 'متن دکمه‌ی اصلی', 'max' => 60, 'half' => true],
            ['key' => 'primary_url', 'type' => 'url', 'label' => 'لینک دکمه‌ی اصلی', 'half' => true],
            ['key' => 'secondary_label', 'type' => 'text', 'label' => 'متن دکمه‌ی دوم', 'max' => 60, 'half' => true],
            ['key' => 'secondary_url', 'type' => 'url', 'label' => 'لینک دکمه‌ی دوم', 'half' => true],
        ],
        'defaults' => [
            'title' => 'آماده شروع پروژه هستید؟',
            'text' => 'نیازتان را ثبت کنید تا پیش‌فاکتور و زمان‌بندی اولیه را برایتان ارسال کنیم؛ بدون هزینه و تعهد.',
            'primary_label' => 'ثبت سفارش پروژه', 'primary_url' => '/order',
            'secondary_label' => 'تماس با ما', 'secondary_url' => '/contact',
        ],
    ],

    'form' => [
        'category' => 'cta',
        'label' => 'فرم (از فرم‌ساز)',
        'icon' => 'ph-list-checks',
        'description' => 'یکی از فرم‌های فرم‌ساز با عنوان و توضیح؛ ورودی‌ها در پنل ثبت می‌شوند.',
        'fields' => [
            ...$head(),
            ['key' => 'form', 'type' => 'form', 'label' => 'فرم', 'hint' => 'فرم‌ها در منوی «فرم‌ساز» ساخته می‌شوند.'],
            ['key' => 'layout', 'type' => 'select', 'label' => 'چیدمان', 'options' => ['center' => 'وسط صفحه (عرض متوسط)', 'split' => 'دو ستون: متن کنار فرم'], 'default' => 'center', 'half' => true],
            $alt,
            ['key' => 'side_text', 'type' => 'editor', 'label' => 'متن کنار فرم (چیدمان دو ستون)'],
        ],
        'defaults' => [
            'eyebrow' => 'تماس با ما', 'title' => 'فرم را پر کنید', 'description' => 'در کمتر از یک روز کاری پاسخ می‌دهیم.',
            'form' => 0, 'layout' => 'center', 'alt' => false,
            'side_text' => '<p>اطلاعات تماس یا توضیح بیشتر درباره‌ی این فرم را اینجا بنویسید.</p>',
        ],
    ],

    'newsletter' => [
        'category' => 'cta',
        'label' => 'عضویت در خبرنامه',
        'icon' => 'ph-envelope-simple',
        'description' => 'فرم ایمیل خبرنامه با تأیید دومرحله‌ای.',
        'fields' => [
            ['key' => 'title', 'type' => 'text', 'label' => 'عنوان', 'max' => 120],
            ['key' => 'text', 'type' => 'textarea', 'label' => 'متن', 'max' => 300],
            $alt,
        ],
        'defaults' => [
            'title' => 'خبرنامه آرون سافت',
            'text' => 'تازه‌ترین مقالات را در ایمیل خود دریافت کنید. عضویت با ایمیل تأیید فعال می‌شود.',
            'alt' => true,
        ],
    ],

    /* ================================================================ تصویر و ویدئو */

    'video' => [
        'category' => 'media',
        'label' => 'ویدئو',
        'icon' => 'ph-play-circle',
        'description' => 'ویدئوی آپارات یا یوتیوب با عنوان و توضیح.',
        'fields' => [
            ...$head(),
            ['key' => 'url', 'type' => 'url', 'label' => 'لینک ویدئو', 'hint' => 'لینک صفحه‌ی ویدئو در آپارات یا یوتیوب'],
            $alt,
        ],
        'defaults' => ['eyebrow' => '', 'title' => 'ویدئوی معرفی', 'description' => '', 'url' => '', 'alt' => false],
    ],

    'gallery' => [
        'category' => 'media',
        'label' => 'گالری تصاویر',
        'icon' => 'ph-camera',
        'description' => 'شبکه‌ی تصاویر با زیرنویس اختیاری.',
        'fields' => [
            ...$head(),
            ['key' => 'columns', 'type' => 'select', 'label' => 'تعداد ستون', 'options' => ['two' => '۲ ستون', 'three' => '۳ ستون', 'four' => '۴ ستون'], 'default' => 'three', 'half' => true],
            $alt,
            ['key' => 'items', 'type' => 'repeater', 'label' => 'تصاویر', 'item_label' => 'caption', 'max_items' => 24, 'fields' => [
                ['key' => 'image', 'type' => 'image', 'label' => 'تصویر'],
                ['key' => 'caption', 'type' => 'text', 'label' => 'زیرنویس', 'max' => 120],
            ]],
        ],
        'defaults' => ['eyebrow' => '', 'title' => 'گالری', 'description' => '', 'columns' => 'three', 'alt' => false, 'items' => []],
    ],

    /* ================================================================ چیدمان و کد */

    'spacer' => [
        'category' => 'layout',
        'label' => 'فاصله / جداکننده',
        'icon' => 'ph-arrows-left-right',
        'description' => 'فضای خالی بین بلوک‌ها، با خط جداکننده‌ی اختیاری.',
        'fields' => [
            ['key' => 'size', 'type' => 'select', 'label' => 'اندازه', 'options' => ['sm' => 'کم', 'md' => 'متوسط', 'lg' => 'زیاد'], 'default' => 'md', 'half' => true],
            ['key' => 'divider', 'type' => 'toggle', 'label' => 'خط جداکننده', 'half' => true],
        ],
        'defaults' => ['size' => 'md', 'divider' => false],
    ],

    'html' => [
        'category' => 'layout',
        'label' => 'کد HTML',
        'icon' => 'ph-code-block',
        'description' => 'کد HTML دلخواه (مثلاً embed). فقط کد مطمئن قرار دهید؛ بدون تغییر در صفحه چاپ می‌شود.',
        'fields' => [
            ['key' => 'code', 'type' => 'code', 'label' => 'کد HTML'],
            ['key' => 'container', 'type' => 'toggle', 'label' => 'داخل کادر عرض سایت', 'default' => true, 'half' => true],
        ],
        'defaults' => ['code' => '', 'container' => true],
    ],
];
