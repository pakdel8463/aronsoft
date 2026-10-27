<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = sys_get_temp_dir().'/sitemap-test-'.uniqid().'.xml';
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    private function generate(array $args = []): string
    {
        $this->artisan('sitemap:generate', ['--output' => $this->file] + $args)->assertExitCode(0);

        return file_get_contents($this->file);
    }

    public function test_only_public_canonical_urls_with_app_url_base(): void
    {
        $pub = $this->makePost(['title' => 'Pub']);
        $draft = $this->makePost(['status' => 'draft']);
        $future = $this->makePost(['published_at' => now()->addMonth()]);
        $portfolio = $this->makePortfolio();
        $hidden = $this->makePortfolio(['status' => 'draft']);
        Product::create(['title' => 'Prod one', 'slug' => 'prod-one', 'type' => 'linux_script', 'description' => 'd', 'is_active' => true]);
        Product::create(['title' => 'Hidden prod', 'slug' => 'hidden-prod', 'type' => 'linux_script', 'description' => 'd', 'is_active' => false]);

        $xml = $this->generate();

        // base url comes from config('app.url')
        $this->assertStringContainsString('<loc>https://aron-soft.com/contact</loc>', $xml);
        $this->assertStringContainsString('<loc>https://aron-soft.com/order</loc>', $xml);
        $this->assertStringContainsString('<loc>https://aron-soft.com/portfolios</loc>', $xml);
        $this->assertStringContainsString('https://aron-soft.com/blog/'.$pub->slug, $xml);
        $this->assertStringContainsString('https://aron-soft.com/portfolio/'.$portfolio->slug, $xml);
        $this->assertStringContainsString('https://aron-soft.com/shop/prod-one', $xml);
        $this->assertStringContainsString('https://aron-soft.com/services/seo', $xml);

        // unpublished / inactive / utility never appear
        $this->assertStringNotContainsString($draft->slug, $xml);
        $this->assertStringNotContainsString($future->slug, $xml);
        $this->assertStringNotContainsString($hidden->slug, $xml);
        $this->assertStringNotContainsString('hidden-prod', $xml);
        foreach (['/aron-captcha/refresh', 'filemanager', 'localhost', '127.0.0.1', '/panel', '/login', '/register', 'inv-8x2k9z', '_generate-sitemap', '/dashboard', '/profile', '/newsletter', '/invoice', '/up<'] as $bad) {
            $this->assertStringNotContainsString($bad, $xml, "sitemap leaked {$bad}");
        }
    }

    public function test_url_option_overrides_base_and_no_duplicates(): void
    {
        $xml = $this->generate(['--url' => 'https://example.org']);
        $this->assertStringContainsString('<loc>https://example.org/contact</loc>', $xml);
        $this->assertStringNotContainsString('aron-soft.com', $xml);

        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $m);
        $this->assertSame(count($m[1]), count(array_unique($m[1])));
    }

    public function test_inactive_landing_rows_and_about_us_are_not_duplicated(): void
    {
        LandingPage::create(['slug' => 'about-us', 'title' => 'x', 'template' => 'about', 'is_active' => true]);
        $xml = $this->generate();
        $this->assertSame(1, substr_count($xml, '/about-us<'));
    }

    public function test_blog_category_only_when_it_has_published_posts(): void
    {
        $empty = Category::create(['name' => 'Empty', 'slug' => 'empty-cat', 'is_active' => true]);
        $xml = $this->generate();
        $this->assertStringNotContainsString('empty-cat', $xml);

        $this->makePost(); // attaches the "general" category
        $this->assertStringContainsString('/blog/category/general', $this->generate());
    }
}
