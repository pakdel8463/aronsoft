<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * داده‌ی نمایشی برای توسعه و تست قالب فرانت (فقط محیط لوکال/تست).
 *
 *   php artisan db:seed --class=DemoContentSeeder
 *
 * - کاملاً idempotent است (با slug به‌صورت updateOrCreate)؛ اجرای چندباره رکورد تکراری نمی‌سازد.
 * - در محیط production اجرا نمی‌شود مگر با DEMO_SEED_FORCE=1.
 * - تصاویر در public/images/demo قرار دارند (SVG نمایشی).
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') && ! env('DEMO_SEED_FORCE')) {
            $this->command?->warn('DemoContentSeeder در محیط production اجرا نمی‌شود.');
            return;
        }

        DB::transaction(function () {
            $author = User::query()->orderBy('id')->first()
                ?? User::create([
                    'name'     => 'تیم آرون سافت',
                    'email'    => 'demo@aron-soft.test',
                    'password' => 'password',
                ]);

            // DEMO_PARTS=menus,blog  → فقط منوها و وبلاگ (بدون نمونه‌کار و محصول ساختگی)؛ خالی = همه
            $parts = array_filter(array_map('trim', explode(',', (string) env('DEMO_PARTS', ''))));
            $want = fn (string $p) => $parts === [] || in_array($p, $parts, true);

            $cats = $this->categories();
            $tags = $this->tags();
            if ($want('portfolio')) {
                $this->portfolios($cats, $tags);
            }
            if ($want('blog')) {
                $this->posts($author, $cats, $tags);
            }
            if ($want('products')) {
                $this->products();
            }
            if ($want('menus')) {
                $this->menus($author);
            }
        });

        $this->command?->info('Demo content seeded.');
    }

    /* ------------------------------------------------------------------ */

    private function categories(): array
    {
        $rows = [
            // نمونه‌کار
            'web-platforms' => ['پلتفرم‌های وب', 'portfolio', 'سامانه‌ها و پلتفرم‌های تحت وب اختصاصی'],
            'wordpress'     => ['وردپرس و ووکامرس', 'portfolio', 'سایت‌های شرکتی و فروشگاهی روی وردپرس'],
            'devops'        => ['زیرساخت و DevOps', 'portfolio', 'راه‌اندازی سرور، داکر و CI/CD'],
            // وبلاگ
            'laravel'       => ['لاراول', 'blog', 'آموزش و نکات کاربردی فریم‌ورک لاراول'],
            'devops-blog'   => ['دواپس', 'blog', 'مطالب فنی درباره سرور، داکر و استقرار'],
            'seo'           => ['سئو', 'blog', 'سئوی فنی و محتوا برای سایت‌های ایرانی'],
            'wordpress-blog'=> ['وردپرس', 'blog', 'امنیت، سرعت و توسعه وردپرس'],
        ];

        $out = [];
        foreach ($rows as $slug => [$name, $type, $desc]) {
            $out[$slug] = Category::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'type' => $type, 'description' => $desc, 'is_active' => 1, 'language' => 'fa', 'deleted_at' => null]
            );
        }
        return $out;
    }

    private function tags(): array
    {
        $out = [];
        foreach (['laravel' => 'Laravel', 'docker' => 'Docker', 'wordpress' => 'WordPress', 'seo' => 'SEO', 'security' => 'امنیت', 'performance' => 'سرعت'] as $slug => $name) {
            $out[$slug] = Tag::withTrashed()->updateOrCreate(['slug' => $slug], ['name' => $name, 'language' => 'fa', 'deleted_at' => null]);
        }
        return $out;
    }

    private function portfolios(array $cats, array $tags): void
    {
        $rows = [
            [
                'slug' => 'internal-ticketing-platform', 'title' => 'پلتفرم مدیریت تیکت و پشتیبانی',
                'short' => 'سامانه تیکتینگ سازمانی با پنل مدیریت، سطح‌بندی اولویت و گزارش‌گیری.',
                'tech' => 'Laravel, MySQL, Redis, Tailwind', 'cat' => 'web-platforms', 'tags' => ['laravel'], 'image' => 'portfolio-1', 'progress' => 100,
            ],
            [
                'slug' => 'home-appliance-store', 'title' => 'فروشگاه اینترنتی لوازم خانگی',
                'short' => 'فروشگاه ووکامرس با فیلتر پیشرفته، درگاه پرداخت و بهینه‌سازی سرعت.',
                'tech' => 'WordPress, WooCommerce, Elementor', 'cat' => 'wordpress', 'tags' => ['wordpress', 'performance'], 'image' => 'portfolio-2', 'progress' => 100,
            ],
            [
                'slug' => 'tech-magazine', 'title' => 'مجله خبری و آموزشی فناوری',
                'short' => 'مجله چندنویسنده با سیستم دسته‌بندی، جستجو و سئوی فنی.',
                'tech' => 'WordPress, PHP, SEO', 'cat' => 'wordpress', 'tags' => ['wordpress', 'seo'], 'image' => 'portfolio-3', 'progress' => 100,
            ],
            [
                'slug' => 'law-office-landing', 'title' => 'وب‌سایت معرفی دفتر خدمات حقوقی',
                'short' => 'سایت شرکتی سریع و واکنش‌گرا با تمرکز بر دریافت درخواست مشاوره.',
                'tech' => 'Laravel, Blade, Tailwind', 'cat' => 'web-platforms', 'tags' => ['laravel', 'seo'], 'image' => 'portfolio-4', 'progress' => 100,
            ],
            [
                'slug' => 'field-service-app', 'title' => 'اپلیکیشن مدیریت خدمات میدانی',
                'short' => 'پنل وب و API برای اپلیکیشن موبایل مدیریت ماموریت‌ها و گزارش‌های میدانی.',
                'tech' => 'Laravel, REST API, Flutter', 'cat' => 'web-platforms', 'tags' => ['laravel'], 'image' => 'portfolio-5', 'progress' => 80,
            ],
            [
                'slug' => 'docker-ci-cd-migration', 'title' => 'مهاجرت به داکر و راه‌اندازی CI/CD',
                'short' => 'کانتینرایز کردن اپلیکیشن، پایپ‌لاین استقرار خودکار و مانیتورینگ.',
                'tech' => 'Docker, GitLab CI, Nginx, Linux', 'cat' => 'devops', 'tags' => ['docker', 'security'], 'image' => 'portfolio-6', 'progress' => 100,
            ],
        ];

        foreach ($rows as $i => $r) {
            $desc = '<p>' . e($r['short']) . '</p>'
                . '<h3>چالش</h3><p>نیاز بود سامانه‌ای پایدار، سریع و قابل توسعه تحویل داده شود؛ با تمرکز بر تجربه کاربری ساده و مدیریت آسان محتوا و داده‌ها توسط تیم کارفرما.</p>'
                . '<h3>راهکار</h3><p>پس از تحلیل نیازمندی‌ها، معماری مناسب انتخاب و پروژه در مراحل کوتاه و قابل‌تحویل پیاده‌سازی شد. تست‌های عملکرد و امنیت پیش از استقرار انجام گرفت.</p>'
                . '<ul><li>تحلیل و طراحی تجربه کاربری</li><li>پیاده‌سازی و تست</li><li>استقرار و مستندسازی</li></ul>';

            $p = Portfolio::updateOrCreate(['slug' => $r['slug']], [
                'title'             => $r['title'],
                'short_description' => $r['short'],
                'description'       => $desc,
                'featured_image'    => 'images/demo/' . $r['image'] . '.svg',
                'technologies'      => $r['tech'],
                'status'            => 'published',
                'progress'          => (string) $r['progress'],
                'start_date'        => now()->subMonths(8 - $i)->toDateString(),
                'seo_title'         => $r['title'],
                'seo_description'   => $r['short'],
            ]);
            // short_description در $fillable مدل نیست؛ مستقیم ذخیره می‌کنیم.
            DB::table('portfolios')->where('id', $p->id)->update(['short_description' => $r['short'], 'created_at' => now()->subDays(40 - $i * 5)]);

            $p->categories()->syncWithoutDetaching([$cats[$r['cat']]->id]);
            $p->tags()->syncWithoutDetaching(array_map(fn ($t) => $tags[$t]->id, $r['tags']));
        }
    }

    private function posts(User $author, array $cats, array $tags): void
    {
        $rows = [
            ['slug' => 'laravel-performance-checklist', 'title' => 'چک‌لیست بهینه‌سازی عملکرد اپلیکیشن لاراول', 'cat' => 'laravel', 'tags' => ['laravel', 'performance'],
             'desc' => 'مهم‌ترین گام‌های افزایش سرعت یک پروژه لاراول: کش، کوئری‌ها، صف‌ها و تنظیمات سرور.',
             'sections' => [['کش کردن هوشمندانه', 'کش پیکربندی، مسیرها و ویوها اولین و ساده‌ترین قدم است. با دستورات config:cache و route:cache و view:cache زمان بوت اپلیکیشن به‌طور محسوسی کم می‌شود.'], ['کوئری‌های بهینه', 'مشکل N+1 رایج‌ترین دلیل کندی صفحات لیستی است. با eager loading و انتخاب ستون‌های مورد نیاز، تعداد کوئری‌ها و حجم داده را کاهش دهید.'], ['استفاده از صف', 'کارهای سنگین مثل ارسال ایمیل و پردازش فایل را به صف بسپارید تا پاسخ کاربر معطل نماند.']]],
            ['slug' => 'docker-production-images', 'title' => 'ساخت ایمیج‌های سبک داکر برای محیط عملیاتی', 'cat' => 'devops-blog', 'tags' => ['docker'],
             'desc' => 'با multi-stage build و انتخاب ایمیج پایه مناسب، حجم ایمیج و سطح حمله را کاهش دهید.',
             'sections' => [['Multi-stage build', 'در مرحله اول وابستگی‌ها را نصب و بیلد کنید و فقط خروجی نهایی را در ایمیج اجرایی کپی کنید.'], ['ایمیج پایه مناسب', 'ایمیج‌های alpine یا distroless حجم کمتری دارند؛ اما سازگاری کتابخانه‌ها را حتماً تست کنید.'], ['اجرا با کاربر غیر root', 'اجرای پردازش‌ها با کاربر محدود، ریسک نفوذ را به‌شدت کاهش می‌دهد.']]],
            ['slug' => 'why-your-site-is-slow', 'title' => 'چرا سایت شما کند است؟ بررسی Core Web Vitals', 'cat' => 'seo', 'tags' => ['seo', 'performance'],
             'desc' => 'عوامل اصلی کندی سایت و راهکارهای عملی بهبود LCP، CLS و INP.',
             'sections' => [['تصاویر بهینه', 'تصاویر را با ابعاد درست، فرمت مدرن و lazy loading ارائه دهید و برای هرکدام width و height مشخص کنید.'], ['اسکریپت‌های اضافی', 'هر اسکریپت شخص ثالث هزینه‌ای دارد. فقط موارد ضروری را بارگذاری کنید و با تأخیر یا async اجرا کنید.'], ['هاست و کش', 'کش سمت سرور و CDN زمان پاسخ را کاهش می‌دهد.']]],
            ['slug' => 'wordpress-security-basics', 'title' => 'اصول امنیت وردپرس؛ جلوگیری از هک شدن سایت', 'cat' => 'wordpress-blog', 'tags' => ['wordpress', 'security'],
             'desc' => 'راهکارهای عملی برای ایمن‌سازی وردپرس: بروزرسانی، دسترسی‌ها، پشتیبان‌گیری و فایروال.',
             'sections' => [['به‌روزرسانی منظم', 'هسته، قالب و افزونه‌ها را همیشه به‌روز نگه دارید و افزونه‌های بلااستفاده را حذف کنید.'], ['محدودسازی دسترسی', 'از رمزهای قوی، احراز هویت دومرحله‌ای و محدودیت تلاش ورود استفاده کنید.'], ['پشتیبان‌گیری', 'نسخه پشتیبان خودکار و تست‌شده، آخرین خط دفاعی شماست.']]],
            ['slug' => 'technical-seo-checklist-fa', 'title' => 'چک‌لیست سئوی فنی برای سایت‌های فارسی', 'cat' => 'seo', 'tags' => ['seo'],
             'desc' => 'از ساختار URL و canonical تا sitemap و داده ساختاریافته؛ همه مواردی که باید بررسی کنید.',
             'sections' => [['ساختار URL و canonical', 'آدرس‌های ثابت، کوتاه و بدون پارامتر اضافه بسازید و برای هر صفحه canonical مشخص کنید.'], ['داده ساختاریافته', 'با JSON-LD نوع صفحه (مقاله، سازمان، سؤالات متداول) را به موتور جستجو اعلام کنید.'], ['Sitemap و robots', 'نقشه سایت به‌روز و فایل robots درست، خزش را بهینه می‌کند.']]],
            ['slug' => 'laravel-queues-in-practice', 'title' => 'صف‌ها در لاراول؛ از Redis تا Supervisor', 'cat' => 'laravel', 'tags' => ['laravel'],
             'desc' => 'راه‌اندازی صف‌ها روی سرور، مدیریت خطاها و تلاش مجدد.',
             'sections' => [['انتخاب درایور', 'برای شروع، درایور database ساده است؛ در بار بالا از Redis استفاده کنید.'], ['اجرای پایدار workerها', 'با Supervisor مطمئن شوید workerها پس از خطا یا ریبوت دوباره اجرا می‌شوند.'], ['مدیریت شکست', 'از failed_jobs و retry/backoff برای کارهای ناموفق استفاده کنید.']]],
            ['slug' => 'ci-cd-for-small-teams', 'title' => 'CI/CD برای تیم‌های کوچک؛ از کجا شروع کنیم؟', 'cat' => 'devops-blog', 'tags' => ['docker', 'security'],
             'desc' => 'یک پایپ‌لاین ساده شامل تست، بیلد و استقرار خودکار که در یک روز قابل راه‌اندازی است.',
             'sections' => [['تست خودکار', 'اجرای تست‌ها روی هر push، اولین لایه اطمینان است.'], ['بیلد ایمیج', 'ایمیج را یک‌بار بسازید و همان را در همه محیط‌ها اجرا کنید.'], ['استقرار با امکان بازگشت', 'همیشه راهی برای برگشت سریع به نسخه قبل داشته باشید.']]],
            ['slug' => 'woocommerce-speed-tips', 'title' => 'افزایش سرعت فروشگاه ووکامرس در عمل', 'cat' => 'wordpress-blog', 'tags' => ['wordpress', 'performance'],
             'desc' => 'کش صفحه، بهینه‌سازی تصاویر و پاکسازی دیتابیس برای فروشگاه‌های ووکامرسی.',
             'sections' => [['کش صفحه و آبجکت', 'کش صفحه برای کاربران مهمان و کش آبجکت با Redis، بار سرور را کم می‌کند.'], ['تصاویر محصول', 'ابعاد استاندارد و فشرده‌سازی مناسب، سرعت صفحه محصول را بالا می‌برد.'], ['پاکسازی دیتابیس', 'سشن‌ها، ترنزینت‌ها و ریویژن‌های اضافه را به‌صورت دوره‌ای پاک کنید.']]],
        ];

        foreach ($rows as $i => $r) {
            $body = '<p>' . e($r['desc']) . '</p>';
            foreach ($r['sections'] as [$h, $t]) {
                $body .= '<h2>' . e($h) . '</h2><p>' . e($t) . '</p>';
            }
            $body .= '<h2>جمع‌بندی</h2><p>اجرای این موارد به‌صورت تدریجی و اندازه‌گیری نتیجه هر تغییر، بهترین رویکرد است. اگر برای پیاده‌سازی به کمک نیاز دارید، تیم آرون سافت آماده مشاوره است.</p>';

            $post = Post::withTrashed()->where('slug', $r['slug'])->first() ?? new Post();
            $post->fill([
                'title'            => $r['title'],
                'body'             => $body,
                'status'           => 'published',
                'author_id'        => $author->id,
                'featured_image'   => 'images/demo/post-' . ($i + 1) . '.svg',
                'alt'              => $r['title'],
                'meta_title'       => $r['title'],
                'meta_description' => $r['desc'],
                'published_at'     => now()->subDays(60 - $i * 6),
                'commentable'      => true,
            ]);
            $post->slug = $r['slug'];
            $post->deleted_at = null;
            $post->save();
            DB::table('posts')->where('id', $post->id)->update(['created_at' => now()->subDays(60 - $i * 6)]);

            $post->categories()->syncWithoutDetaching([$cats[$r['cat']]->id]);
            $post->tags()->syncWithoutDetaching(array_map(fn ($t) => $tags[$t]->id, $r['tags']));
        }
    }

    private function products(): void
    {
        // نکته: Product::creating اسلاگ را از عنوان می‌سازد؛ پس عنوان‌ها لاتین هستند تا اسلاگ ثابت بماند.
        $rows = [
            ['slug' => 'aron-corporate-theme', 'title' => 'Aron Corporate Theme', 'type' => 'wordpress_theme', 'price' => 0,
             'short' => 'قالب وردپرس شرکتی سبک و واکنش‌گرا با پشتیبانی کامل از فارسی و RTL.', 'image' => 'product-1', 'version' => '1.2.0', 'sale' => false],
            ['slug' => 'aron-linux-hardening-script', 'title' => 'Aron Linux Hardening Script', 'type' => 'linux_script', 'price' => 490000,
             'short' => 'اسکریپت امن‌سازی اولیه سرورهای اوبونتو: فایروال، SSH و به‌روزرسانی خودکار.', 'image' => 'product-2', 'version' => '1.0.3', 'sale' => true],
        ];

        foreach ($rows as $r) {
            $p = Product::where('slug', $r['slug'])->first() ?? new Product();
            $p->fill([
                'title' => $r['title'], 'slug' => $r['slug'], 'type' => $r['type'], 'price' => $r['price'],
                'short_description' => $r['short'],
                'description' => '<p>' . e($r['short']) . '</p><h3>امکانات</h3><ul><li>نصب و راه‌اندازی ساده</li><li>مستندات فارسی</li><li>به‌روزرسانی و پشتیبانی</li></ul>',
                'featured_image' => 'images/demo/' . $r['image'] . '.svg', 'version' => $r['version'],
                'is_for_sale' => $r['sale'], 'is_active' => true,
                'seo_title' => $r['title'], 'seo_description' => $r['short'],
            ]);
            $p->save();
            if ($p->slug !== $r['slug']) {
                DB::table('products')->where('id', $p->id)->update(['slug' => $r['slug']]);
            }
        }
    }

    private function menus(User $user): void
    {
        $defs = [
            'top' => ['منوی اصلی', 'top', [
                ['خدمات', '/services/web-development', [
                    ['طراحی سایت اختصاصی', '/services/web-development'],
                    ['طراحی سایت وردپرس', '/services/wordpress-website-design'],
                    ['خدمات DevOps', '/services/devops-services'],
                    ['خدمات سئو', '/services/seo'],
                    ['کپچای آرون', '/services/aron-captcha'],
                ]],
                ['نمونه‌کارها', '/portfolios', []],
                ['وبلاگ', '/blog', []],
                ['فروشگاه', '/shop', []],
                ['درباره ما', '/about-us', []],
                ['تماس با ما', '/contact', []],
            ]],
            'footer-col-1' => ['فوتر - شرکت', 'footer-col-1', [
                ['درباره ما', '/about-us', []],
                ['نمونه‌کارها', '/portfolios', []],
                ['وبلاگ', '/blog', []],
                ['تماس با ما', '/contact', []],
            ]],
            'footer-col-2' => ['فوتر - خدمات', 'footer-col-2', [
                ['طراحی سایت اختصاصی', '/services/web-development', []],
                ['طراحی سایت وردپرس', '/services/wordpress-website-design', []],
                ['خدمات DevOps', '/services/devops-services', []],
                ['خدمات سئو', '/services/seo', []],
                ['ثبت سفارش پروژه', '/order', []],
            ]],
        ];

        // وقتی نمونه‌کار/محصول ساخته نمی‌شود، لینکش را هم در منو نگذار (صفحه‌ی خالی نشان ندهد).
        $parts = array_filter(array_map('trim', explode(',', (string) env('DEMO_PARTS', ''))));
        $skip = [];
        if ($parts !== [] && ! in_array('portfolio', $parts, true)) { $skip[] = '/portfolios'; }
        if ($parts !== [] && ! in_array('products', $parts, true)) { $skip[] = '/shop'; }
        foreach ($defs as $k => [$n, $pos, $its]) {
            $defs[$k][2] = array_values(array_filter($its, fn ($it) => ! in_array($it[1], $skip, true)));
        }

        foreach ($defs as $slug => [$name, $position, $items]) {
            $menu = Menu::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'position' => $position, 'user_id' => $user->id, 'deleted_at' => null]
            );

            // آیتم‌ها را از نو می‌سازیم تا اجرای مجدد تکراری ایجاد نکند.
            MenuItem::withTrashed()->where('menu_id', $menu->id)->forceDelete();

            foreach ($items as $sort => [$title, $url, $children]) {
                $parent = MenuItem::create([
                    'menu_id' => $menu->id, 'user_id' => $user->id, 'display_title' => $title,
                    'url' => $url, 'status' => 1, 'lang' => 'fa', 'sort' => $sort, 'depth' => 0,
                ]);
                foreach ($children as $cs => [$ct, $cu]) {
                    MenuItem::create([
                        'menu_id' => $menu->id, 'parent_id' => $parent->id, 'user_id' => $user->id,
                        'display_title' => $ct, 'url' => $cu, 'status' => 1, 'lang' => 'fa', 'sort' => $cs, 'depth' => 1,
                    ]);
                }
            }
        }
    }
}
