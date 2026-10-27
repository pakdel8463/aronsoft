<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Portfolio;
use App\Models\PortfolioImage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['is_superuser' => 1, 'is_staff' => 1]);
    }

    public function test_guests_cannot_reach_catalog_admin(): void
    {
        $this->get('/panel/portfolios')->assertRedirect();
        $this->get('/panel/products')->assertRedirect();
        $this->get('/panel/landing')->assertRedirect();
    }

    public function test_portfolio_crud_with_gallery_tags_and_cleanup(): void
    {
        Storage::fake('public');
        $a = $this->staff();
        $cat = Category::create(['name' => 'Web', 'slug' => 'web', 'type' => 'portfolio', 'is_active' => true]);

        $up = $this->actingAs($a)->postJson(route('panel.portfolio.upload-image'), [
            'images' => [UploadedFile::fake()->image('a.png'), UploadedFile::fake()->image('b.jpg')],
        ])->assertOk();
        $ids = collect($up->json('files'))->pluck('id')->all();
        $this->assertCount(2, $ids);

        $this->actingAs($a)->postJson(route('panel.portfolio.upload-image'), [
            'images' => [UploadedFile::fake()->create('x.php', 5, 'text/x-php')],
        ])->assertStatus(422);

        $this->actingAs($a)->post(route('panel.portfolios.store'), [
            'title' => 'پروژه تست', 'status' => 'published', 'start_date' => '1404/05/20', 'progress' => 80,
            'technologies' => ['Laravel', 'MySQL'], 'tags' => ['alpha', 'beta'], 'categories' => [$cat->id],
            'gallery_submitted' => 1, 'gallery' => array_reverse($ids),
        ])->assertRedirect();

        $p = Portfolio::firstOrFail();
        $this->assertSame(['Laravel', 'MySQL'], $p->technologies);
        $this->assertSame(2, $p->images()->count());
        $this->assertSame(array_reverse($ids), $p->images->pluck('id')->all());
        $this->assertSame(1, $p->categories()->count());
        $this->assertSame(1, $p->tags()->first()->count);

        // update: تگ شمارنده را دوباره زیاد نکند، یک تصویر حذف شود
        $this->actingAs($a)->put(route('panel.portfolios.update', $p), [
            'title' => 'پروژه تست', 'status' => 'draft', 'tags' => ['alpha', 'beta'],
            'gallery_submitted' => 1, 'gallery' => [$ids[0]],
        ])->assertRedirect();
        $p->refresh();
        $this->assertSame(1, $p->images()->count());
        $this->assertSame(1, $p->tags()->first()->count);
        $this->assertSame(1, PortfolioImage::count());

        $this->actingAs($a)->get(route('panel.portfolios.index'))->assertOk()->assertSee('پروژه تست');
        $this->actingAs($a)->get(route('panel.portfolios.edit', $p))->assertOk();

        $this->actingAs($a)->delete(route('panel.portfolios.destroy', $p))->assertRedirect();
        $this->assertSame(0, Portfolio::count());
        $this->assertSame(0, PortfolioImage::count());
        $this->assertSame([], Storage::disk('public')->allFiles('portfolio/gallery'));
    }

    public function test_portfolio_slug_is_unique_and_invalid_date_rejected(): void
    {
        $a = $this->staff();
        $this->actingAs($a)->post(route('panel.portfolios.store'), ['title' => 'Same', 'status' => 'draft']);
        $this->actingAs($a)->post(route('panel.portfolios.store'), ['title' => 'Same', 'status' => 'draft']);
        $this->assertSame(['same', 'same-2'], Portfolio::orderBy('id')->pluck('slug')->all());

        $this->actingAs($a)->post(route('panel.portfolios.store'), ['title' => 'X', 'status' => 'draft', 'start_date' => 'abc'])
            ->assertSessionHasErrors('start_date');
    }

    public function test_product_crud_validates_and_cleans_files(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $a = $this->staff();

        $img = $this->actingAs($a)->postJson(route('panel.products.upload-image'), ['images' => [UploadedFile::fake()->image('p.png')]])
            ->assertOk()->json('files.0.path');

        $this->actingAs($a)->post(route('panel.products.store'), [
            'title' => 'Plugin', 'type' => 'wordpress_plugin', 'description' => '<p>x</p><script>alert(1)</script>',
            'price' => '۱۰۰٬۰۰۰', 'discount_price' => '200000', 'featured_image' => $img,
        ])->assertSessionHasErrors('discount_price');

        $this->actingAs($a)->post(route('panel.products.store'), [
            'title' => 'Plugin', 'type' => 'wordpress_plugin', 'description' => '<p>x</p><script>alert(1)</script>',
            'price' => '۱۰۰٬۰۰۰', 'discount_price' => '80000', 'featured_image' => $img, 'is_active' => 1,
            'download_file' => UploadedFile::fake()->create('p.zip', 10, 'application/zip'),
        ])->assertRedirect();

        $p = Product::firstOrFail();
        $this->assertSame(100000, $p->price);
        $this->assertSame(80000, $p->finalPrice());
        $this->assertStringNotContainsString('<script', $p->description);
        $this->assertTrue($p->is_active);
        Storage::disk('local')->assertExists($p->file_path);

        // آپلود فایل اجرایی ممنوع
        $this->actingAs($a)->put(route('panel.products.update', $p), [
            'title' => 'Plugin', 'type' => 'wordpress_plugin', 'description' => 'd',
            'download_file' => UploadedFile::fake()->create('x.php', 1),
        ])->assertSessionHasErrors('download_file');

        $this->actingAs($a)->put(route('panel.products.update', $p), [
            'title' => 'Plugin 2', 'type' => 'linux_script', 'description' => 'd', 'price' => 0,
        ])->assertRedirect(route('panel.products.index'));
        $p->refresh();
        $this->assertSame('Plugin 2', $p->title);
        $this->assertFalse($p->is_active);
        $this->assertNull($p->featured_image);

        $this->actingAs($a)->post(route('panel.products.toggle', $p))->assertRedirect();
        $this->assertTrue($p->fresh()->is_active);

        $file = $p->file_path;
        $this->actingAs($a)->delete(route('panel.products.destroy', $p))->assertRedirect();
        $this->assertSame(0, Product::count());
        Storage::disk('local')->assertMissing($file);
    }

    public function test_landing_crud(): void
    {
        $a = $this->staff();
        $this->actingAs($a)->post(route('panel.landing.store'), ['title' => 'L', 'slug' => 'bad slug', 'template' => 'webdev'])
            ->assertSessionHasErrors('slug');
        $this->actingAs($a)->post(route('panel.landing.store'), ['title' => 'L', 'slug' => 'my-land', 'template' => 'webdev', 'is_active' => 1])
            ->assertRedirect();
        $l = LandingPage::firstOrFail();
        $this->actingAs($a)->put(route('panel.landing.update', $l), ['title' => 'L2', 'slug' => 'my-land', 'template' => 'devops'])->assertRedirect();
        $this->assertFalse($l->fresh()->is_active);
        $this->actingAs($a)->get(route('panel.landing.index'))->assertOk()->assertSee('L2');
        $this->actingAs($a)->delete(route('panel.landing.destroy', $l))->assertRedirect();
        $this->assertSame(0, LandingPage::count());
    }
}
