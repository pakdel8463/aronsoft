<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\ProductPage;
use App\Support\ProductPageSchema;
use Illuminate\Database\Seeder;

/**
 * پیش‌نویس اولیه‌ی لندینگ محصولات آرون سافت: کالریتو (قالب citrus)، مانیار (violet)، هوماواچ (ink).
 * محتوای هر صفحه = محتوای نمونه‌ی قالبش (config/product-templates/*.php) + مشخصات محصول.
 *
 *  - صفحاتی که از قبل (با همان slug) وجود دارند دست‌نخورده می‌مانند؛ ویرایش‌های پنل پاک نمی‌شود.
 *    برای جایگزینی با نمونه‌ی قالب:  PRODUCT_PAGES_RESET=1
 *  - پیش‌فرض «پیش‌نویس» ساخته می‌شوند تا قبل از انتشار متن‌ها را مرور کنید
 *    (ادمین با باز کردن آدرس، پیش‌نمایش را می‌بیند). برای انتشار فوری:  PRODUCT_PAGES_PUBLISH=1
 *  - اجرا در production:  php artisan db:seed --class=ProductPagesSeeder --force
 *  - لینک «محصولات» به منوی اصلی اضافه می‌شود (اگر منو وجود داشته باشد و لینک را نداشته باشد).
 */
class ProductPagesSeeder extends Seeder
{
    public function run(): void
    {
        $publish = filter_var(env('PRODUCT_PAGES_PUBLISH', false), FILTER_VALIDATE_BOOLEAN);
        $reset = filter_var(env('PRODUCT_PAGES_RESET', false), FILTER_VALIDATE_BOOLEAN);

        foreach ($this->definitions() as $i => $def) {
            $existing = ProductPage::where('slug', $def['slug'])->first();
            if ($existing && ! $reset) {
                $this->command?->line("— {$def['slug']}: از قبل وجود دارد، رد شد.");

                continue;
            }

            $tpl = $def['template'];
            $pal = array_values(ProductPageSchema::paletteValues($tpl, []));
            $data = $def + [
                'status' => $existing->status ?? ($publish ? 'published' : 'draft'),
                'published_at' => $existing->published_at ?? ($publish ? now() : null),
                'sort_order' => ($i + 1) * 10,
                'layout' => ProductPageSchema::defaultLayout($tpl),
                'sections' => ProductPageSchema::sample($tpl),
                'accent' => $pal[0] ?? '#2F4FF5',
                'accent_2' => $pal[1] ?? null,
            ];
            $existing ? $existing->update($data) : ProductPage::create($data);
            $this->command?->info("✓ {$def['slug']} ".($existing ? 'بازنشانی' : 'ساخته').' شد (قالب '.$tpl.')');
        }

        $this->addMenuLink();
    }

    private function addMenuLink(): void
    {
        $menu = Menu::where('slug', 'top')->first() ?? Menu::where('position', 'top')->first();
        if (! $menu || MenuItem::where('menu_id', $menu->id)->where('url', '/products')->exists()) {
            return;
        }

        $contact = MenuItem::where('menu_id', $menu->id)->whereNull('parent_id')->where('url', '/contact')->first();
        $sort = $contact ? (int) $contact->sort : ((int) MenuItem::where('menu_id', $menu->id)->whereNull('parent_id')->max('sort') + 1);
        MenuItem::where('menu_id', $menu->id)->whereNull('parent_id')->where('sort', '>=', $sort)->increment('sort');

        MenuItem::create([
            'menu_id' => $menu->id, 'user_id' => $menu->user_id, 'display_title' => 'محصولات',
            'url' => '/products', 'status' => 1, 'lang' => 'fa', 'sort' => $sort, 'depth' => 0,
        ]);
        $this->command?->info('✓ لینک «محصولات» به منوی اصلی اضافه شد.');
    }

    private function definitions(): array
    {
        return [
            [
                'slug' => 'calorieto', 'template' => 'citrus', 'name' => 'کالریتو', 'name_en' => 'Calorieto',
                'tagline' => 'شبکه اجتماعی ورزشی ایرانی: رژیم و برنامه‌ی تمرینی شخصی، چالش‌های گروهی و جامعه‌ای که انگیزه‌تان را زنده نگه می‌دارد.',
                'category_label' => 'اپلیکیشن ورزش و تغذیه', 'icon' => 'ph-barbell',
                'links' => ['website' => 'https://calorieto.com'],
                'seo_title' => 'کالریتو، شبکه اجتماعی ورزشی و تغذیه',
                'seo_description' => 'کالریتو شبکه‌ی اجتماعی ورزشی ایرانی است؛ رژیم و برنامه‌ی تمرینی شخصی با هوش مصنوعی، چالش‌های گروهی و سکه‌ی کالریتو.',
            ],
            [
                'slug' => 'moneyiar', 'template' => 'violet', 'name' => 'مانیار', 'name_en' => 'Moneyiar',
                'tagline' => 'محاسبه و مدیریت درآمد و هزینه‌ی شرکت‌ها و کسب‌وکارهای کوچک، روی وب و اندروید.',
                'category_label' => 'نرم‌افزار مالی', 'icon' => 'ph-wallet',
                'links' => ['website' => 'https://moneyiar.com'],
                'seo_title' => 'مانیار، مدیریت درآمد و هزینه‌ی کسب‌وکار',
                'seo_description' => 'مانیار درآمد و هزینه‌ی شرکت یا کسب‌وکار کوچک شما را ثبت و محاسبه می‌کند. فارسی و انگلیسی، روی وب و اندروید.',
            ],
            [
                'slug' => 'humawatch', 'template' => 'ink', 'name' => 'هوماواچ', 'name_en' => 'HumaWatch',
                'tagline' => 'سیستم تردد سازمانی با شیفت‌بندی و قوانین کار خودکار؛ نسخه ابری و Self-Hosted.',
                'category_label' => 'نرم‌افزار تردد سازمانی', 'icon' => 'ph-fingerprint',
                'links' => ['website' => 'https://humawatch.com'],
                'seo_title' => 'هوماواچ، سیستم تردد و شیفت‌بندی سازمانی',
                'seo_description' => 'هوماواچ ورود و خروج کارکنان را ثبت می‌کند و قوانین کار سازمان شما را خودکار اعمال می‌کند. نسخه ابری و Self-Hosted.',
            ],
        ];
    }
}
