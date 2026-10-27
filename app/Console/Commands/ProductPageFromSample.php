<?php

namespace App\Console\Commands;

use App\Models\ProductPage;
use App\Support\ProductPageSchema;
use Illuminate\Console\Command;

/** ساخت (یا بازنشانی) یک صفحه‌ی محصول از محتوای نمونه‌ی یک قالب. */
class ProductPageFromSample extends Command
{
    protected $signature = 'product-pages:from-sample {template : ink|violet|citrus} {slug} {--name=} {--publish} {--reset : اگر slug وجود دارد محتوایش با نمونه جایگزین شود}';

    protected $description = 'ساخت صفحه‌ی محصول از محتوای نمونه‌ی قالب';

    public function handle(): int
    {
        $tpl = (string) $this->argument('template');
        if (! isset(ProductPageSchema::templates()[$tpl])) {
            $this->error('قالب ناشناخته. موارد معتبر: '.implode(', ', array_keys(ProductPageSchema::templates())));

            return self::FAILURE;
        }
        $slug = (string) $this->argument('slug');
        $page = ProductPage::where('slug', $slug)->first();
        if ($page && ! $this->option('reset')) {
            $this->warn("صفحه‌ای با slug «{$slug}» وجود دارد (برای جایگزینی --reset بزنید).");

            return self::FAILURE;
        }
        $page ??= new ProductPage(['slug' => $slug, 'name' => $this->option('name') ?: $slug, 'sort_order' => (int) ProductPage::max('sort_order') + 10]);
        $pal = array_values(ProductPageSchema::paletteValues($tpl, []));
        $page->fill([
            'template' => $tpl,
            'sections' => ProductPageSchema::sample($tpl),
            'layout' => ProductPageSchema::defaultLayout($tpl),
            'accent' => $pal[0] ?? '#2F4FF5',
            'accent_2' => $pal[1] ?? null,
            'status' => $this->option('publish') ? 'published' : ($page->status ?: 'draft'),
        ]);
        if ($this->option('name')) {
            $page->name = $this->option('name');
        }
        $page->save();
        $this->info("OK: /products/{$page->slug} (قالب {$tpl})");

        return self::SUCCESS;
    }
}
