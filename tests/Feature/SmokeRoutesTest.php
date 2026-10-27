<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every important public + panel page must render (HTTP 200) on an empty database.
 */
class SmokeRoutesTest extends TestCase
{
    use RefreshDatabase;

    public static function publicPaths(): array
    {
        return array_map(fn ($p) => [$p], [
            '/', '/blog', '/portfolios', '/shop', '/contact', '/order',
            '/services/web-development', '/services/wordpress-website-design', '/services/devops-services',
            '/services/seo', '/about-us', '/login', '/register', '/forgot-password',
        ]);
    }

    public static function panelPaths(): array
    {
        return array_map(fn ($p) => [$p], [
            '/panel', '/panel/posts', '/panel/posts/create', '/panel/categories', '/panel/comments',
            '/panel/portfolios', '/panel/portfolios/create', '/panel/products', '/panel/products/create',
            '/panel/landing', '/panel/users', '/panel/roles', '/panel/permissions', '/panel/menus',
            '/panel/leads', '/panel/contact-messages', '/panel/newsletter', '/panel/orders',
            '/panel/proformas', '/panel/tickets', '/panel/tickets/departments',
        ]);
    }

    #[DataProvider('publicPaths')]
    public function test_public_pages_render(string $path): void
    {
        $this->get($path)->assertOk();
    }

    #[DataProvider('panelPaths')]
    public function test_panel_pages_render_for_superuser(string $path): void
    {
        $u = new User(['name' => 'Boss', 'email' => 'boss@example.test']);
        $u->forceFill(['password' => bcrypt('x'), 'is_superuser' => true, 'is_staff' => true])->save();
        $this->actingAs($u)->get($path)->assertOk();
    }

    public function test_panel_requires_login(): void
    {
        $this->get('/panel')->assertRedirect();
    }
}
