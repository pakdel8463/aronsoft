<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Page;
use App\Models\User;
use App\Pages\PageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(array $attrs = [], array $types = ['hero', 'services']): Page
    {
        $page = PageBuilder::createPage($attrs['title'] ?? 'صفحه تست', $attrs['slug'] ?? 'test-page');
        $page->update(array_diff_key($attrs, array_flip(['title', 'slug'])));
        foreach ($types as $t) {
            PageBuilder::addBlock($page, $t);
        }

        return $page->fresh();
    }

    public function test_templates_are_seeded_by_the_migration(): void
    {
        $this->assertSame(count(config('page_templates')), Page::templates()->count());
        $home = Page::where('slug', 'template-home')->firstOrFail();
        $this->assertSame(['hero', 'logos', 'services', 'portfolio', 'list_cards', 'posts', 'cta_lead'], $home->blocks()->pluck('type')->all());
    }

    public function test_every_block_type_has_a_view_and_valid_defaults(): void
    {
        foreach (config('blocks') as $type => $def) {
            $this->assertFileExists(resource_path("views/front/t-one/blocks/{$type}.blade.php"), $type);
            $this->assertArrayHasKey($def['category'], PageBuilder::CATEGORIES, $type);
            foreach ($def['fields'] as $f) {
                $this->assertArrayHasKey($f['key'], $def['defaults'], "{$type}.{$f['key']} has no default");
                if ($f['type'] === 'select') {
                    foreach (array_keys($f['options']) as $k) {
                        $this->assertIsString($k, "{$type}.{$f['key']}: select keys must be non-numeric");
                    }
                }
            }
        }
    }

    public function test_page_with_every_block_renders_publicly(): void
    {
        $page = $this->makePage(['is_active' => true], array_keys(config('blocks')));

        $this->get('/test-page')->assertOk()
            ->assertSee('مهندسیِ', false)
            ->assertSee('پرسش‌های متداول')
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('id="services"', false);
    }

    public function test_draft_page_is_hidden_but_previewable_by_admins(): void
    {
        $page = $this->makePage();
        $this->get('/test-page')->assertNotFound();

        $this->actingAs($this->admin())->get(route('panel.pages.preview', $page))
            ->assertOk()->assertSee('پیش‌نمایش')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_existing_routes_are_never_shadowed_and_unknown_paths_404(): void
    {
        $this->assertTrue(PageBuilder::slugIsReserved('blog'));
        $this->assertTrue(PageBuilder::slugIsReserved('panel'));
        $this->assertTrue(PageBuilder::slugIsReserved('contact'));
        $this->assertTrue(PageBuilder::slugIsReserved('fonts'));
        $this->assertFalse(PageBuilder::slugIsReserved('website-design'));

        $this->get('/blog')->assertOk();
        $this->get('/no-such-page')->assertNotFound();
        $this->get('/a/b/c')->assertNotFound();
    }

    public function test_admin_screens_render(): void
    {
        $page = $this->makePage();
        $block = $page->blocks()->first();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('panel.pages.index'))->assertOk()->assertSee('صفحه تست');
        $this->actingAs($admin)->get(route('panel.pages.index', ['tab' => 'templates']))->assertOk()->assertSee('صفحه‌ی فرود');
        $this->actingAs($admin)->get(route('panel.pages.builder', $page))->assertOk()->assertSee('کتابخانه‌ی بلوک‌ها');
        $this->actingAs($admin)->get(route('panel.pages.edit', $page))->assertOk();
        $this->actingAs($admin)->get(route('panel.pages.blocks.edit', [$page, $block]))->assertOk()->assertSee('data-pb-form', false);

        foreach (array_keys(config('blocks')) as $type) {
            $b = PageBuilder::addBlock($page, $type);
            $this->actingAs($admin)->get(route('panel.pages.blocks.edit', [$page, $b]))->assertOk();
        }
    }

    public function test_non_staff_users_cannot_open_the_builder(): void
    {
        $page = $this->makePage();
        $customer = User::factory()->create(['is_superuser' => 0, 'is_staff' => 0]);

        $this->actingAs($customer)->get(route('panel.pages.builder', $page))->assertRedirect(route('home'));
    }

    public function test_create_page_from_template_and_slug_rules(): void
    {
        $admin = $this->admin();
        $tpl = Page::where('slug', 'template-landing')->firstOrFail();

        $this->actingAs($admin)->post(route('panel.pages.store'), ['title' => 'لندینگ تست', 'slug' => 'Landing Test', 'from' => $tpl->id])
            ->assertRedirect();
        $page = Page::where('slug', 'landing-test')->firstOrFail();
        $this->assertFalse($page->is_active);
        $this->assertSame($tpl->blocks()->pluck('type')->all(), $page->blocks()->pluck('type')->all());

        $this->actingAs($admin)->post(route('panel.pages.store'), ['title' => 'x', 'slug' => 'blog'])->assertSessionHasErrors('slug');
        $this->actingAs($admin)->post(route('panel.pages.store'), ['title' => 'x', 'slug' => 'landing-test'])->assertSessionHasErrors('slug');

        // slug from a Persian title
        $this->actingAs($admin)->post(route('panel.pages.store'), ['title' => 'درباره تیم'])->assertRedirect();
        $this->assertTrue(Page::where('slug', 'درباره-تیم')->exists());
    }

    public function test_block_crud_sort_and_sanitizing(): void
    {
        $admin = $this->admin();
        $page = $this->makePage(['is_active' => true]);
        [$hero, $services] = $page->blocks()->get()->all();

        // add a block after the hero
        $this->actingAs($admin)->post(route('panel.pages.blocks.store', $page), ['type' => 'faq', 'after' => $hero->id])->assertRedirect();
        $this->assertSame(['hero', 'faq', 'services'], $page->blocks()->pluck('type')->all());
        $faq = $page->blocks()->where('type', 'faq')->first();

        // update: editor HTML is cleaned, repeater rows are re-indexed, empty rows dropped
        $this->actingAs($admin)->put(route('panel.pages.blocks.update', [$page, $faq]), [
            'anchor' => 'soalat',
            'is_active' => '1',
            'data' => [
                'eyebrow' => 'سؤال', 'title' => 'سؤالات <b>ما</b>', 'description' => '', 'schema' => '0', 'alt' => '1',
                'items' => [['question' => 'یک؟', 'answer' => 'بله'], ['question' => '', 'answer' => '']],
            ],
        ])->assertRedirect();
        $faq->refresh();
        $this->assertSame('soalat', $faq->anchor);
        $this->assertSame('سؤالات <b>ما</b>', $faq->data['title']); // plain text: escaped on output, not stripped
        $this->assertCount(1, $faq->data['items']);
        $this->assertFalse($faq->data['schema']);
        $this->get('/test-page')->assertSee('سؤالات &lt;b&gt;ما&lt;/b&gt;', false)->assertDontSee('FAQPage');

        // rich text editor: scripts removed
        $rt = PageBuilder::addBlock($page, 'rich_text');
        $this->actingAs($admin)->put(route('panel.pages.blocks.update', [$page, $rt]), [
            'is_active' => '1',
            'data' => ['body' => '<p>سلام</p><script>alert(1)</script>', 'width' => 'narrow', 'alt' => '0'],
        ])->assertRedirect();
        $this->assertStringNotContainsString('<script', $rt->fresh()->data['body']);

        // unsafe URL and unknown select value are rejected
        $this->actingAs($admin)->put(route('panel.pages.blocks.update', [$page, $hero]), [
            'is_active' => '1', 'data' => ['primary_url' => 'javascript:alert(1)', 'visual' => 'video'],
        ])->assertSessionHasErrors(['data.primary_url', 'data.visual']);

        // toggle / duplicate / sort / delete
        $this->actingAs($admin)->patch(route('panel.pages.blocks.toggle', [$page, $services]));
        $this->assertFalse($services->fresh()->is_active);
        $this->get('/test-page')->assertDontSee('id="services"', false);

        $this->actingAs($admin)->post(route('panel.pages.blocks.duplicate', [$page, $hero]))->assertRedirect();
        $this->assertSame(2, $page->blocks()->where('type', 'hero')->count());

        $ids = $page->blocks()->pluck('id')->reverse()->values()->all();
        $this->actingAs($admin)->postJson(route('panel.pages.blocks.sort', $page), ['ids' => $ids])->assertOk();
        $this->assertSame($ids, $page->blocks()->pluck('id')->all());

        $this->actingAs($admin)->delete(route('panel.pages.blocks.destroy', [$page, $rt]))->assertRedirect(route('panel.pages.builder', $page));
        $this->assertNull(Block::find($rt->id));

        // a block of another page is not reachable through this page
        $other = $this->makePage(['slug' => 'other']);
        $this->actingAs($admin)->get(route('panel.pages.blocks.edit', [$page, $other->blocks()->first()]))->assertNotFound();
    }

    public function test_builder_page_can_replace_the_home_page(): void
    {
        $admin = $this->admin();
        $page = $this->makePage(['title' => 'خانه جدید', 'slug' => 'new-home'], ['page_header']);

        $this->get('/')->assertOk()->assertDontSee('خانه جدید');

        $this->actingAs($admin)->post(route('panel.pages.home', $page))->assertRedirect();
        $this->assertTrue($page->fresh()->is_home);
        $this->assertTrue($page->fresh()->is_active);
        $this->get('/')->assertOk()->assertSee('خانه جدید');
        $this->get('/new-home')->assertRedirect('/');

        $this->actingAs($admin)->post(route('panel.pages.home', $page));
        // (SEO tag singletons survive between requests inside one test, so check the page body)
        $this->assertFalse($page->fresh()->is_home);
        $this->get('/')->assertOk()->assertDontSee('<h1 class="h1-page">خانه جدید', false);
    }

    public function test_settings_template_duplicate_and_delete(): void
    {
        $admin = $this->admin();
        $page = $this->makePage();

        $this->actingAs($admin)->put(route('panel.pages.update', $page), [
            'title' => 'عنوان تازه', 'slug' => 'fresh-slug', 'is_active' => '1',
            'meta_title' => 'سئو', 'meta_description' => 'توضیح', 'og_image' => '', 'noindex' => '0',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $page->refresh();
        $this->assertSame('fresh-slug', $page->slug);
        $this->assertTrue($page->is_active);
        $this->get('/fresh-slug')->assertOk()->assertSee('<title>سئو', false);

        $this->actingAs($admin)->post(route('panel.pages.template', $page))->assertRedirect();
        $tpl = Page::templates()->where('title', 'قالب: عنوان تازه')->firstOrFail();
        $this->assertSame($page->blocks()->count(), $tpl->blocks()->count());

        $this->actingAs($admin)->post(route('panel.pages.duplicate', $page))->assertRedirect();
        $this->assertTrue(Page::where('slug', 'fresh-slug-copy')->exists());

        $this->actingAs($admin)->delete(route('panel.pages.destroy', $page))->assertRedirect();
        $this->assertNull(Page::find($page->id));
        $this->assertSame(0, Block::where('page_id', $page->id)->count());
    }

    public function test_sitemap_lists_published_pages_only(): void
    {
        $this->makePage(['slug' => 'live', 'is_active' => true]);
        $this->makePage(['slug' => 'hidden-seo', 'is_active' => true, 'settings' => ['noindex' => true]]);
        $this->makePage(['slug' => 'draft']);
        $out = storage_path('framework/testing-sitemap.xml');

        $this->artisan('sitemap:generate', ['--url' => 'https://aron-soft.com', '--output' => $out])->assertSuccessful();
        $xml = file_get_contents($out);
        @unlink($out);
        $this->assertStringContainsString('https://aron-soft.com/live', $xml);
        $this->assertStringNotContainsString('hidden-seo', $xml);
        $this->assertStringNotContainsString('/draft', $xml);
        $this->assertStringNotContainsString('template-', $xml);
    }
}
