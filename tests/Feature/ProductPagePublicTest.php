<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\ProductPage;
use App\Models\User;
use App\Support\ProductPageSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** صفحات عمومی محصول: /products و /products/{slug} */
class ProductPagePublicTest extends TestCase
{
    use RefreshDatabase;

    private function make(array $over = []): ProductPage
    {
        $tpl = $over['template'] ?? 'violet';
        $sections = ProductPageSchema::sample($tpl);
        $sections['hero']['title'] = 'عنوان اصلی';
        $sections['faq']['items'] = [['q' => 'سؤال؟', 'a' => 'پاسخ']];

        return ProductPage::create($over + [
            'slug' => 'demo-app', 'template' => $tpl, 'name' => 'دمو', 'name_en' => 'Demo', 'tagline' => 'شعار دمو', 'status' => 'published', 'published_at' => now()->subDay(),
            'accent' => '#7C3AED', 'links' => ['website' => 'https://demo.example'],
            'layout' => ProductPageSchema::defaultLayout($tpl),
            'sections' => $sections,
        ]);
    }

    public function test_published_page_renders_with_seo_and_schema(): void
    {
        $this->make();
        $r = $this->get('/products/demo-app')->assertOk();
        $r->assertSee('عنوان اصلی', false)->assertSee('tpl-violet', false)->assertSee('--t-accent:#5B21C9', false);
        $r->assertSee('application/ld+json', false)->assertSee('SoftwareApplication', false)->assertSee('FAQPage', false);
        $r->assertSee('<link rel="canonical" href="'.url('/products/demo-app').'"', false);
        $r->assertDontSee('noindex', false);
    }

    public function test_each_template_renders_its_sample_without_errors(): void
    {
        foreach (array_keys(ProductPageSchema::templates()) as $t) {
            $this->make(['slug' => 'p-'.$t, 'template' => $t, 'name' => 'نام '.$t]);
            $this->get('/products/p-'.$t)->assertOk()->assertSee('tpl-'.$t, false)->assertSee('id="lead"', false);
        }
    }

    public function test_palette_override_is_applied_and_invalid_values_ignored(): void
    {
        $s = ProductPageSchema::sample('violet');
        $s['_palette'] = ['accent' => '#112233', 'deep' => 'not-a-color'];
        $this->make(['sections' => $s]);
        $this->get('/products/demo-app')->assertOk()->assertSee('--t-accent:#112233', false)->assertSee('--t-deep:#4C1D95', false);
    }

    public function test_disabled_and_empty_sections_are_not_rendered(): void
    {
        $p = $this->make();
        $this->get('/products/demo-app')->assertOk()->assertSee('id="sec-faq"', false)->assertSee('id="sec-ledger"', false);

        $p->update(['layout' => collect($p->layoutRows())->map(fn ($r) => $r['key'] === 'faq' ? ['key' => 'faq', 'enabled' => false] : $r)->all()]);
        $this->get('/products/demo-app')->assertOk()->assertDontSee('id="sec-faq"', false)->assertDontSee('FAQPage', false);

        // بخش بدون محتوا هم نمایش داده نمی‌شود
        $s = $p->sections;
        $s['audience'] = ['title' => '', 'items' => [], 'nav' => ''];
        $p->update(['sections' => $s]);
        $this->get('/products/demo-app')->assertOk()->assertDontSee('id="sec-audience"', false);
    }

    public function test_section_order_follows_layout(): void
    {
        $p = $this->make();
        $keys = array_column($p->layoutRows(), 'key');
        $keys = array_values(array_diff($keys, ['faq', 'ledger']));
        array_splice($keys, 1, 0, ['faq', 'ledger']);
        $p->update(['layout' => array_map(fn ($k) => ['key' => $k, 'enabled' => true], $keys)]);

        $html = $this->get('/products/demo-app')->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'id="sec-ledger"'), strpos($html, 'id="sec-faq"'));
    }

    public function test_draft_is_404_for_guests_but_previewable_by_authorised_staff(): void
    {
        $this->make(['status' => 'draft', 'published_at' => null]);
        $this->get('/products/demo-app')->assertNotFound();

        $u = User::factory()->create(['is_staff' => 1]);
        $perm = Permission::firstOrCreate(['slug' => 'product-pages.manage'], ['name' => 'x']);
        $u->extraPermissions()->attach($perm->id);
        foreach (Permission::all() as $pp) {
            \Illuminate\Support\Facades\Gate::define($pp->slug, fn ($user) => $user->hasPermission($pp->slug));
        }
        $this->actingAs($u)->get('/products/demo-app')->assertOk()->assertSee('پیش‌نمایش پیش‌نویس', false)->assertSee('noindex', false);

        $plain = User::factory()->create();
        $this->actingAs($plain)->get('/products/demo-app')->assertNotFound();
    }

    public function test_scheduled_future_page_is_hidden(): void
    {
        $this->make(['published_at' => now()->addDay()]);
        $this->get('/products/demo-app')->assertNotFound();
        $this->get('/products')->assertOk()->assertDontSee('دمو');
    }

    public function test_noindex_flag_adds_robots_meta(): void
    {
        $this->make(['noindex' => true]);
        $this->get('/products/demo-app')->assertOk()->assertSee('noindex', false);
    }

    public function test_index_lists_only_published(): void
    {
        $this->make();
        $this->make(['slug' => 'hidden-one', 'name' => 'مخفی‌شده', 'status' => 'draft', 'published_at' => null]);
        $this->get('/products')->assertOk()->assertSee('دمو')->assertDontSee('مخفی‌شده')->assertSee('/products/demo-app', false);
    }

    public function test_index_is_ok_when_empty(): void
    {
        $this->get('/products')->assertOk()->assertSee('به‌زودی');
    }

    public function test_unknown_slug_is_404(): void
    {
        $this->get('/products/nope')->assertNotFound();
    }

    public function test_content_is_escaped(): void
    {
        $p = $this->make();
        $s = $p->sections;
        $s['hero']['title'] = '<script>alert(1)</script>';          // مستقیم در DB (دور زدن sanitize)
        $s['faq']['items'][0]['q'] = '<img src=x onerror=alert(2)>';
        $p->update(['sections' => $s]);
        $this->get('/products/demo-app')->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('<img src=x', false)->assertSee('&lt;script&gt;', false);
    }

    public function test_sitemap_includes_published_indexable_pages_only(): void
    {
        $this->make();
        $this->make(['slug' => 'noidx', 'name' => 'ان', 'noindex' => true]);
        $this->make(['slug' => 'draft-x', 'name' => 'پ', 'status' => 'draft', 'published_at' => null]);
        @unlink(public_path('sitemap.xml'));
        $this->artisan('sitemap:generate')->assertExitCode(0);
        $xml = file_get_contents(public_path('sitemap.xml'));
        $this->assertStringContainsString('/products/demo-app', $xml);
        $this->assertStringNotContainsString('/products/noidx', $xml);
        $this->assertStringNotContainsString('/products/draft-x', $xml);
    }

    public function test_seeder_is_idempotent_and_creates_three_drafts(): void
    {
        $this->seed(\Database\Seeders\ProductPagesSeeder::class);
        $this->seed(\Database\Seeders\ProductPagesSeeder::class);
        $this->assertSame(3, ProductPage::count());
        $this->assertSame(['calorieto', 'moneyiar', 'humawatch'], ProductPage::orderBy('sort_order')->pluck('slug')->all());
        $this->assertSame(['citrus', 'violet', 'ink'], ProductPage::orderBy('sort_order')->pluck('template')->all());
        $this->assertSame(0, ProductPage::published()->count());
        $this->get('/products/calorieto')->assertNotFound();
    }

    public function test_seeded_pages_open_in_admin_editor_and_resave_cleanly(): void
    {
        $this->seed(\Database\Seeders\ProductPagesSeeder::class);
        $u = User::factory()->create(['is_staff' => 1]);
        $perm = Permission::firstOrCreate(['slug' => 'product-pages.manage'], ['name' => 'x']);
        $u->extraPermissions()->attach($perm->id);
        foreach (Permission::all() as $pp) {
            \Illuminate\Support\Facades\Gate::define($pp->slug, fn ($user) => $user->hasPermission($pp->slug));
        }

        foreach (ProductPage::all() as $page) {
            $this->actingAs($u)->get(route('panel.product-pages.edit', $page))->assertOk()->assertSee($page->name);
        }
        $this->actingAs($u)->get(route('panel.product-pages.index'))->assertOk()->assertSee('هوماواچ');
    }
}
