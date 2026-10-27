<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThrottleAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_post_is_throttled_with_persian_json(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('contact.store'), [])->assertStatus(422);
        }

        $res = $this->postJson(route('contact.store'), [])->assertStatus(429);
        $this->assertStringContainsString('تعداد درخواست', $res->json('message'));
        $this->assertNotNull($res->headers->get('Retry-After'));
    }

    public function test_every_public_post_route_has_a_throttle(): void
    {
        foreach (['leads.store', 'contact.store', 'order.store', 'comments.store', 'newsletter.subscribe', 'proforma.store'] as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertNotNull($route, $name);
            $this->assertTrue(
                collect($route->gatherMiddleware())->contains(fn ($m) => is_string($m) && str_starts_with($m, 'throttle:')),
                "{$name} is not throttled"
            );
        }
    }

    public function test_public_sitemap_trigger_route_is_gone(): void
    {
        $this->get('/_generate-sitemap')->assertNotFound();
    }

    public function test_invoice_preview_is_protected(): void
    {
        $this->get('/inv-8x2k9z')->assertRedirect(route('login'));
        $this->get('/services/inv-8x2k9z')->assertNotFound();
        $this->actingAs($this->admin())->get('/inv-8x2k9z')->assertOk();
    }

    public function test_sitemap_generation_from_panel_is_admin_only(): void
    {
        $this->post('/panel/sitemap/generate')->assertRedirect(route('login'));
    }

    public function test_security_headers_are_sent(): void
    {
        $res = $this->get('/contact');
        $res->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $res->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_landing_redirects_do_not_loop(): void
    {
        $this->get('/landing/seo')->assertRedirect(route('landing.show', 'seo'))->assertStatus(301);
        $this->get('/landing/about-us')->assertRedirect('/about-us')->assertStatus(301);
        $this->get('/services/about-us')->assertRedirect('/about-us')->assertStatus(301);
        $this->get('/services/seo')->assertOk();
        $this->get('/about-us')->assertOk();
    }

    public function test_custom_persian_error_pages(): void
    {
        $this->get('/definitely-missing-page')->assertNotFound()->assertSee('صفحه پیدا نشد')->assertSee('404');
    }

    public function test_csrf_failure_on_ajax_returns_persian_json(): void
    {
        // the test environment skips CSRF verification, so render the exception directly
        $request = \Illuminate\Http\Request::create('/x', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($request, new \Illuminate\Session\TokenMismatchException('CSRF token mismatch.'));

        $this->assertSame(419, $response->getStatusCode());
        $this->assertStringContainsString('نشست', json_decode($response->getContent(), true)['message']);
    }
}
