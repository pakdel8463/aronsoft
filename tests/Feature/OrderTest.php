<?php

namespace Tests\Feature;

use App\Mail\AdminNotificationMail;
use App\Mail\OrderReceivedMail;
use App\Models\Lead;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $over = []): array
    {
        return array_merge([
            'type' => 'shop',
            'budget' => '50_150',
            'deadline' => '2_months',
            'features' => ['gateway', 'inventory'],
            'description' => 'یک فروشگاه لوازم خانگی با درگاه پرداخت.',
            'name' => 'سارا محمدی',
            'mobile' => '+98 912 123 4567',
            'email' => 'sara@example.com',
            'contact_time' => 'morning',
            'consent' => '1',
            'captcha' => 'abc12',
        ], $over);
    }

    public function test_order_page_renders_with_all_types(): void
    {
        $res = $this->get('/order')->assertOk();
        foreach (['طراحی سایت', 'فروشگاه اینترنتی', 'نرم‌افزار / اپلیکیشن', 'سئو و محتوا', 'DevOps و زیرساخت', 'مشاوره رایگان'] as $label) {
            $res->assertSee($label);
        }
        $this->assertSame('/order', route('order.create', [], false));
    }

    public function test_order_is_stored_with_lead_and_mails(): void
    {
        Mail::fake();

        $res = $this->withSession($this->captcha())->postJson(route('order.store'), $this->payload())
            ->assertOk()->assertJson(['status' => 'success']);

        $order = Order::firstOrFail();
        $this->assertMatchesRegularExpression('/^ARN-[A-Z2-9]{6}$/', $order->code);
        $this->assertSame('09121234567', $order->mobile);
        $this->assertSame('new', $order->status);
        $this->assertSame(['gateway', 'inventory'], $order->features);
        $this->assertNotNull($order->lead_id);
        $this->assertNotNull($order->consented_at);
        $this->assertStringContainsString($order->code, $res->json('redirect'));
        $this->assertStringContainsString('t='.$order->access_token, $res->json('redirect'));

        $lead = Lead::findOrFail($order->lead_id);
        $this->assertSame('09121234567', $lead->mobile);
        $this->assertStringContainsString($order->code, $lead->message);

        Mail::assertSent(OrderReceivedMail::class, fn ($m) => $m->hasTo('sara@example.com'));
        Mail::assertSent(AdminNotificationMail::class, fn ($m) => $m->hasTo('admin@aron-soft.test'));
    }

    public function test_tracking_page_requires_code_and_token(): void
    {
        $this->withSession($this->captcha())->postJson(route('order.store'), $this->payload());
        $order = Order::firstOrFail();

        $this->get('/order/'.$order->code)->assertNotFound();
        $this->get('/order/'.$order->code.'?t=wrong')->assertNotFound();
        $this->get('/order/ARN-ZZZZZZ?t='.$order->access_token)->assertNotFound();

        $this->get($order->publicUrl())
            ->assertOk()
            ->assertSee($order->code)
            ->assertSee('خلاصه درخواست')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_validation_rejects_bad_input(): void
    {
        $this->withSession($this->captcha())
            ->postJson(route('order.store'), $this->payload(['type' => 'nope', 'consent' => null, 'mobile' => '12']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'consent', 'mobile']);

        // feature that does not belong to the chosen type
        $this->withSession($this->captcha())
            ->postJson(route('order.store'), $this->payload(['type' => 'seo', 'features' => ['gateway']]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['features.0']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_attachment_is_stored_on_private_local_disk_and_limited_to_5mb(): void
    {
        Storage::fake('local');

        $this->withSession($this->captcha())
            ->post(route('order.store'), $this->payload(['attachment' => UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf')]), ['Accept' => 'application/json'])
            ->assertOk();

        $order = Order::firstOrFail();
        $this->assertSame('brief.pdf', $order->attachment_name);
        Storage::disk('local')->assertExists($order->attachment_path);

        $this->withSession($this->captcha())
            ->post(route('order.store'), $this->payload(['attachment' => UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf')]), ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('attachment');

        $this->withSession($this->captcha())
            ->post(route('order.store'), $this->payload(['attachment' => UploadedFile::fake()->create('evil.php', 1, 'text/x-php')]), ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('attachment');
    }

    public function test_honeypot_creates_nothing(): void
    {
        $this->withSession($this->captcha())
            ->postJson(route('order.store'), $this->payload(['website' => 'spam']))->assertOk();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_consultation_order_without_email_works(): void
    {
        Mail::fake();
        $this->withSession($this->captcha())
            ->postJson(route('order.store'), $this->payload(['type' => 'consult', 'features' => ['idea'], 'budget' => null, 'email' => null]))
            ->assertOk();
        $this->assertDatabaseHas('orders', ['type' => 'consult', 'email' => null]);
        Mail::assertNotSent(OrderReceivedMail::class);
        Mail::assertSent(AdminNotificationMail::class);
    }

    public function test_status_labels_are_persian(): void
    {
        $o = new Order(['status' => 'quoted', 'type' => 'devops']);
        $this->assertSame('پیش‌فاکتور ارسال شد', $o->status_label);
        $this->assertSame('DevOps و زیرساخت', $o->type_label);
    }

    // ---------------- admin ----------------

    public function test_admin_order_routes_require_staff(): void
    {
        $this->get('/panel/orders')->assertRedirect(route('login'));
    }

    public function test_admin_can_list_filter_update_export_and_delete(): void
    {
        $this->withSession($this->captcha())->postJson(route('order.store'), $this->payload());
        $this->withSession($this->captcha())->postJson(route('order.store'), $this->payload(['type' => 'seo', 'features' => ['audit'], 'name' => 'نفر دوم']));
        $order = Order::where('type', 'shop')->firstOrFail();
        $admin = $this->admin();

        $this->actingAs($admin)->get('/panel/orders')->assertOk()->assertSee($order->code)->assertSee('نفر دوم');
        $this->actingAs($admin)->get('/panel/orders?type=seo')->assertOk()->assertSee('نفر دوم')->assertDontSee($order->code);
        $this->actingAs($admin)->get('/panel/orders?q=سارا')->assertOk()->assertSee($order->code)->assertDontSee('نفر دوم');
        $this->actingAs($admin)->get('/panel/orders/'.$order->id)->assertOk()->assertSee($order->code);

        $this->actingAs($admin)->patch(route('panel.orders.status', $order), ['status' => 'contacted'])->assertRedirect();
        $this->assertSame('contacted', $order->fresh()->status);
        $this->assertTrue((bool) $order->fresh()->lead->is_contacted);

        $this->actingAs($admin)->patch(route('panel.orders.status', $order), ['status' => 'bogus'])->assertSessionHasErrors('status');
        $this->actingAs($admin)->patch(route('panel.orders.note', $order), ['admin_note' => 'تماس گرفته شد'])->assertRedirect();
        $this->assertSame('تماس گرفته شد', $order->fresh()->admin_note);

        $csv = $this->actingAs($admin)->get('/panel/orders-export')->assertOk();
        $this->assertStringContainsString('text/csv', $csv->headers->get('content-type'));
        $body = $csv->streamedContent();
        $this->assertStringContainsString($order->code, $body);
        $this->assertStringContainsString('سارا محمدی', $body);

        $this->actingAs($admin)->delete(route('panel.orders.destroy', $order))->assertRedirect(route('panel.orders.index'));
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
