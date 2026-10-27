<?php

namespace Tests\Feature;

use App\Mail\InvoiceSent;
use App\Models\Proforma;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProformaTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_comes_from_server_catalog_not_client_price(): void
    {
        Mail::fake();

        $res = $this->postJson(route('proforma.store'), [
            'client_email' => 'x@example.com',
            'items' => [
                ['id' => 'wp_base', 'title' => 'whatever', 'price' => 1],
                ['id' => 'wp_sms', 'price' => 1],
            ],
        ])->assertOk();

        $p = Proforma::firstOrFail();
        $this->assertSame(12000000 + 2000000, (int) $p->total_amount);
        $this->assertSame(14000000, $res->json('total'));
        $this->assertEqualsCanonicalizing([12000000, 2000000], array_column($p->items, 'price'));
        Mail::assertSent(InvoiceSent::class, fn ($m) => $m->hasTo('x@example.com'));
    }

    public function test_legacy_title_matching_still_works_and_ignores_price(): void
    {
        $this->postJson(route('proforma.store'), [
            'client_phone' => '09121234567',
            'items' => [
                ['title' => 'طراحی سایت شرکتی (پایه)', 'price' => 10],
                ['title' => 'بهینه‌سازی سرعت (راکت)', 'price' => -500, 'description' => 'x'],
            ],
        ])->assertOk();

        $this->assertSame(13500000, (int) Proforma::firstOrFail()->total_amount);
    }

    public function test_unknown_items_are_rejected(): void
    {
        $this->postJson(route('proforma.store'), [
            'client_email' => 'x@example.com',
            'items' => [['title' => 'Free everything', 'price' => 0]],
        ])->assertStatus(422)->assertJsonValidationErrors('items');

        $this->assertDatabaseCount('proformas', 0);
    }

    public function test_required_base_is_added_and_orphan_sub_features_are_dropped(): void
    {
        $this->postJson(route('proforma.store'), [
            'client_email' => 'x@example.com',
            // sub-feature without its parent module, base item omitted
            'items' => [['id' => 'woo_pay'], ['id' => 'wp_sms']],
        ])->assertOk();

        $p = Proforma::firstOrFail();
        $ids = array_column($p->items, 'id');
        $this->assertContains('wp_base', $ids);
        $this->assertNotContains('woo_pay', $ids);
        $this->assertSame(12000000 + 2000000, (int) $p->total_amount);
    }

    public function test_mixed_categories_are_rejected(): void
    {
        $this->postJson(route('proforma.store'), [
            'client_email' => 'x@example.com',
            'items' => [['id' => 'wp_base'], ['id' => 'dev_docker']],
        ])->assertStatus(422);
    }

    public function test_requires_contact_and_does_not_leak_exceptions(): void
    {
        $this->postJson(route('proforma.store'), ['items' => [['id' => 'wp_base']]])
            ->assertStatus(422)->assertJsonValidationErrors(['client_email']);
    }

    public function test_mail_failure_does_not_abort_the_invoice(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->postJson(route('proforma.store'), ['client_email' => 'x@example.com', 'items' => [['id' => 'seo_audit']]])
            ->assertOk()->assertJson(['status' => 'success']);

        $this->assertDatabaseCount('proformas', 1);
    }

    public function test_invoice_page_is_noindex(): void
    {
        $this->postJson(route('proforma.store'), ['client_email' => 'x@example.com', 'items' => [['id' => 'seo_audit']]]);
        $p = Proforma::firstOrFail();

        $this->get(route('proforma.show', $p->uuid))->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
