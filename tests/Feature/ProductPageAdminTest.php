<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\ProductPage;
use App\Models\User;
use App\Support\ProductPageSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** پنل «لندینگ محصولات» (/panel/product-pages): ساخت، ویرایش، اعتبارسنجی، چیدمان بخش‌ها و دسترسی */
class ProductPageAdminTest extends TestCase
{
    use RefreshDatabase;

    private function staff(bool $permission = true): User
    {
        $u = User::factory()->create(['is_staff' => 1]);
        if ($permission) {
            $p = Permission::firstOrCreate(['slug' => 'product-pages.manage'], ['name' => 'مدیریت صفحات محصول']);
            $u->extraPermissions()->attach($p->id);
        }
        // Gate تعریف‌ها هنگام بوت ساخته می‌شوند؛ برای تست دوباره ثبت می‌کنیم
        foreach (Permission::all() as $perm) {
            \Illuminate\Support\Facades\Gate::define($perm->slug, fn ($user) => $user->hasPermission($perm->slug));
        }

        return $u;
    }

    /** payload کامل فرم (قالب violet)؛ $over با array_replace_recursive ادغام می‌شود */
    private function payload(array $over = [], string $tpl = 'violet'): array
    {
        $def = ProductPageSchema::defaultLayout($tpl);
        $enabled = [];
        foreach ($def as $r) {
            if ($r['enabled']) {
                $enabled[$r['key']] = '1';
            }
        }

        $base = [
            'template' => $tpl,
            'name' => 'مانیار', 'name_en' => 'Moneyiar', 'slug' => '', 'tagline' => 'مدیریت درآمد و هزینه',
            'category_label' => 'نرم‌افزار', 'status' => 'draft', 'published_at' => '', 'sort_order' => '10',
            'logo' => '', 'card_image' => '', 'og_image' => '', 'icon' => 'ph-wallet',
            'links' => ['website' => 'https://moneyiar.com', 'bazaar' => ''],
            'seo_title' => '', 'seo_description' => '', 'seo_keywords' => '', 'canonical_url' => '', 'noindex' => '0',
            'layout' => array_column($def, 'key'), 'enabled' => $enabled,
            'sections' => [
                '_palette' => ['accent' => '#10b981'],
                'hero' => ['title' => 'عنوان هیرو', 'badges' => "الف\nب\n\nج", 'primary_url' => 'https://moneyiar.com'],
                'audience' => ['items' => [['name' => 'گروه اول', 'text' => 'متن']]],
            ],
        ];
        foreach (['layout', 'enabled'] as $k) {
            if (array_key_exists($k, $over)) {
                $base[$k] = $over[$k];
                unset($over[$k]);
            }
        }

        return array_replace_recursive($base, $over);
    }

    private function make(array $over = [], string $tpl = 'violet'): ProductPage
    {
        $this->actingAs($this->staff())->post(route('panel.product-pages.store'), $this->payload($over, $tpl))->assertSessionHasNoErrors();

        return ProductPage::query()->latest('id')->firstOrFail();
    }

    public function test_create_without_template_shows_chooser_then_prefills_sample(): void
    {
        $u = $this->staff();
        $r = $this->actingAs($u)->get(route('panel.product-pages.index'))->assertOk()->assertSee('ساخت صفحه‌ی محصول');
        $c = $this->actingAs($u)->get(route('panel.product-pages.create'))->assertOk();
        foreach (ProductPageSchema::templates() as $k => $t) {
            $c->assertSee($t['label'])->assertSee('template='.$k, false);
        }
        $this->actingAs($u)->get(route('panel.product-pages.create', ['template' => 'citrus']))->assertOk()
            ->assertSee('data-pp-form', false)->assertSee('name="template" value="citrus"', false)
            ->assertSee('name="layout[]"', false)->assertSee('sections[_palette][accent]', false)->assertSee('تناسب اندام', false);
    }

    public function test_store_creates_page_with_template_palette_and_sanitized_content(): void
    {
        $page = $this->make();

        $this->assertSame('moneyiar', $page->slug);
        $this->assertSame('violet', $page->template);
        $this->assertSame('#10B981', $page->sections['_palette']['accent']);
        $this->assertSame('#10B981', $page->accent);          // رنگ اول قالب → ستون accent
        $this->assertSame('draft', $page->status);
        $this->assertSame(['website' => 'https://moneyiar.com'], $page->links);
        $this->assertSame(['الف', 'ب', 'ج'], $page->sections['hero']['badges']);
        $this->assertSame('گروه اول', $page->sections['audience']['items'][0]['name']);
        $this->assertSame('ph-wallet', $page->icon);
    }

    public function test_edit_form_renders_template_fields(): void
    {
        $page = $this->make();
        $this->actingAs($this->staff())->get(route('panel.product-pages.edit', $page))->assertOk()
            ->assertSee('گروه اول')->assertSee('sections[_palette][accent]', false)->assertSee('بارگذاری محتوای نمونه‌ی قالب');
    }

    public function test_template_cannot_be_switched_on_update(): void
    {
        $page = $this->make();
        $this->actingAs($this->staff())->put(route('panel.product-pages.update', $page), $this->payload(['template' => 'ink']))->assertSessionHasNoErrors();
        $this->assertSame('violet', $page->refresh()->template);
    }

    public function test_load_sample_replaces_content_and_requires_permission(): void
    {
        $page = $this->make();
        $this->actingAs($this->staff(false))->post(route('panel.product-pages.load-sample', $page))->assertForbidden();
        $this->assertSame('عنوان هیرو', $page->refresh()->sections['hero']['title']);

        $this->actingAs($this->staff())->post(route('panel.product-pages.load-sample', $page))->assertRedirect(route('panel.product-pages.edit', $page));
        $this->assertSame(ProductPageSchema::sample('violet')['hero']['title'], $page->refresh()->sections['hero']['title']);
    }

    public function test_update_changes_page_and_keeps_slug_unique(): void
    {
        $a = $this->make();
        $b = $this->make(['name' => 'دوم', 'name_en' => 'Second']);

        $this->actingAs($this->staff())->put(route('panel.product-pages.update', $b), $this->payload(['name' => 'دوم ۲', 'slug' => $a->slug]))
            ->assertSessionHasErrors('slug');

        $this->actingAs($this->staff())->put(route('panel.product-pages.update', $b), $this->payload(['name' => 'دوم ۲', 'slug' => 'second-2', 'status' => 'published']))
            ->assertRedirect(route('panel.product-pages.edit', $b));
        $b->refresh();
        $this->assertSame('دوم ۲', $b->name);
        $this->assertSame('second-2', $b->slug);
        $this->assertSame('published', $b->status);
        $this->assertNotNull($b->published_at);
    }

    public function test_validation_errors(): void
    {
        $u = $this->staff();
        $this->actingAs($u)->post(route('panel.product-pages.store'), $this->payload(['name' => '']))->assertSessionHasErrors('name');
        $this->actingAs($u)->post(route('panel.product-pages.store'), $this->payload(['sections' => ['_palette' => ['accent' => 'red']]]))->assertSessionHasErrors('sections._palette.accent');
        $this->actingAs($u)->post(route('panel.product-pages.store'), $this->payload(['status' => 'weird']))->assertSessionHasErrors('status');
        $this->actingAs($u)->post(route('panel.product-pages.store'), $this->payload(['slug' => 'Bad Slug!']))->assertSessionHasErrors('slug');
        $this->assertSame(0, ProductPage::count());
    }

    public function test_javascript_url_and_non_whitelisted_icon_are_rejected(): void
    {
        $page = $this->make();
        $u = $this->staff();

        $this->actingAs($u)->put(route('panel.product-pages.update', $page), $this->payload(['sections' => ['hero' => ['primary_url' => 'javascript:alert(1)']]]))
            ->assertSessionHasErrors('sections.hero.primary_url');
        $this->actingAs($u)->put(route('panel.product-pages.update', $page), $this->payload(['links' => ['website' => 'javascript:alert(1)']]))
            ->assertSessionHasErrors('links.website');
        $this->actingAs($u)->put(route('panel.product-pages.update', $page), $this->payload(['icon' => 'fa-bad']))->assertSessionHasErrors('icon');

        $page->refresh();
        $this->assertSame('https://moneyiar.com', $page->sections['hero']['primary_url']);
        $this->assertStringNotContainsString('javascript:', json_encode($page->sections));
    }

    public function test_javascript_url_is_dropped_by_sanitizer_even_if_validation_is_bypassed(): void
    {
        $clean = ProductPageSchema::sanitize(['hero' => ['primary_url' => 'javascript:alert(1)']], 'violet');
        $this->assertSame('', $clean['hero']['primary_url']);
    }

    public function test_reordering_layout_persists_and_hero_cta_stay_pinned(): void
    {
        $page = $this->make();
        $u = $this->staff();
        $order = ['hero', 'faq', 'ledger', 'reports', 'lang', 'audience', 'steps', 'cta'];
        $this->actingAs($u)->put(route('panel.product-pages.update', $page), $this->payload(['layout' => $order]))->assertSessionHasNoErrors();
        $this->assertSame($order, array_column($page->refresh()->layoutRows(), 'key'));

        $this->actingAs($u)->put(route('panel.product-pages.update', $page), $this->payload(['layout' => ['faq', 'cta', 'hero', 'ledger']]))->assertSessionHasNoErrors();
        $keys = array_column($page->refresh()->layoutRows(), 'key');
        $this->assertSame('hero', $keys[0]);
        $this->assertSame('cta', end($keys));
        $this->assertSame('faq', $keys[1]);
    }

    public function test_locked_sections_cannot_be_disabled_and_toggles_persist(): void
    {
        $page = $this->make();
        $u = $this->staff();
        $this->actingAs($u)->put(route('panel.product-pages.update', $page), $this->payload(['enabled' => ['ledger' => '1', 'reports' => '1']]))->assertSessionHasNoErrors();
        $rows = collect($page->refresh()->layoutRows())->keyBy('key');

        $this->assertTrue($rows['hero']['enabled']);
        $this->assertTrue($rows['cta']['enabled']);
        $this->assertTrue($rows['ledger']['enabled']);
        $this->assertFalse($rows['faq']['enabled']);

        // یک بخش اختیاری را روشن/خاموش کنیم
        $this->actingAs($u)->put(route('panel.product-pages.update', $page), $this->payload(['enabled' => ['faq' => '1']]))->assertSessionHasNoErrors();
        $rows = collect($page->refresh()->layoutRows())->keyBy('key');
        $this->assertTrue($rows['faq']['enabled']);
        $this->assertFalse($rows['ledger']['enabled']);
    }

    public function test_empty_repeater_rows_are_dropped(): void
    {
        $page = $this->make(['sections' => ['faq' => ['items' => [
            ['q' => '', 'a' => ''],
            ['q' => 'سؤال واقعی', 'a' => 'پاسخ'],
            ['q' => '   ', 'a' => ''],
        ]]]]);

        $items = $page->sections['faq']['items'];
        $this->assertCount(1, $items);
        $this->assertSame('سؤال واقعی', $items[0]['q']);
        $this->assertSame([0], array_keys($items));
    }

    public function test_old_input_repopulates_form_after_validation_error(): void
    {
        $u = $this->staff();
        $bad = $this->payload(['sections' => ['hero' => ['primary_url' => 'javascript:alert(1)'], 'faq' => ['items' => [['q' => 'سؤال من', 'a' => 'پاسخ من']]]]]);
        $createUrl = route('panel.product-pages.create', ['template' => 'violet']);
        $this->actingAs($u)->from($createUrl)->post(route('panel.product-pages.store'), $bad)->assertRedirect($createUrl);
        $this->actingAs($u)->get($createUrl)->assertOk()
            ->assertSee('value="javascript:alert(1)"', false)
            ->assertSee('سؤال من')
            ->assertSee('مانیار');
    }

    public function test_toggle_publish_duplicate_and_delete(): void
    {
        $u = $this->staff();
        $page = $this->make();

        $this->actingAs($u)->post(route('panel.product-pages.toggle', $page))->assertRedirect();
        $this->assertSame('published', $page->refresh()->status);
        $this->actingAs($u)->post(route('panel.product-pages.toggle', $page))->assertRedirect();
        $this->assertSame('draft', $page->refresh()->status);

        $this->actingAs($u)->post(route('panel.product-pages.duplicate', $page))->assertRedirect();
        $this->assertSame(2, ProductPage::count());
        $copy = ProductPage::where('id', '!=', $page->id)->first();
        $this->assertSame('draft', $copy->status);
        $this->assertNotSame($page->slug, $copy->slug);
        $this->assertSame($page->sections, $copy->sections);

        $this->actingAs($u)->delete(route('panel.product-pages.destroy', $copy))->assertRedirect(route('panel.product-pages.index'));
        $this->assertSame(1, ProductPage::count());
    }

    public function test_index_search_and_status_filter(): void
    {
        $u = $this->staff();
        $this->make();
        $this->make(['name' => 'هوماواچ', 'name_en' => 'Humawatch', 'status' => 'published']);

        $this->actingAs($u)->get(route('panel.product-pages.index', ['q' => 'Humawatch']))->assertOk()->assertSee('هوماواچ')->assertDontSee('کالریتو');
        $this->actingAs($u)->get(route('panel.product-pages.index', ['status' => 'draft']))->assertOk()->assertSee('مانیار')->assertDontSee('هوماواچ');
    }

    public function test_user_without_permission_gets_403_and_guest_is_redirected(): void
    {
        $page = $this->make();
        $nobody = $this->staff(false);

        $this->actingAs($nobody)->get(route('panel.product-pages.index'))->assertForbidden();
        $this->actingAs($nobody)->get(route('panel.product-pages.create', ['template' => 'ink']))->assertForbidden();
        $this->actingAs($nobody)->post(route('panel.product-pages.store'), $this->payload())->assertForbidden();
        $this->actingAs($nobody)->get(route('panel.product-pages.edit', $page))->assertForbidden();
        $this->actingAs($nobody)->put(route('panel.product-pages.update', $page), $this->payload(['name' => 'هک']))->assertForbidden();
        $this->actingAs($nobody)->post(route('panel.product-pages.toggle', $page))->assertForbidden();
        $this->actingAs($nobody)->post(route('panel.product-pages.duplicate', $page))->assertForbidden();
        $this->actingAs($nobody)->delete(route('panel.product-pages.destroy', $page))->assertForbidden();
        $this->assertSame('مانیار', $page->refresh()->name);

        auth()->logout();
        $this->get(route('panel.product-pages.index'))->assertRedirect();
    }

    public function test_superuser_passes_without_explicit_permission(): void
    {
        $this->actingAs($this->admin())->get(route('panel.product-pages.index'))->assertOk();
    }

    public function test_seed_defaults_button_creates_three_drafts_once(): void
    {
        $u = $this->staff();
        $this->actingAs($u)->post(route('panel.product-pages.seed'))->assertRedirect(route('panel.product-pages.index'));
        $this->assertSame(3, ProductPage::count());
        $this->assertSame(0, ProductPage::where('status', 'published')->count());
        $this->actingAs($u)->post(route('panel.product-pages.seed'));
        $this->assertSame(3, ProductPage::count());
    }

    public function test_seed_defaults_requires_permission(): void
    {
        $this->actingAs($this->staff(false))->post(route('panel.product-pages.seed'))->assertForbidden();
    }
}
