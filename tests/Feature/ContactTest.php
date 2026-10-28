<?php

namespace Tests\Feature;

use App\Mail\AdminNotificationMail;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $over = []): array
    {
        return array_merge([
            'name' => 'علی رضایی',
            'mobile' => '۰۹۱۲۱۲۳۴۵۶۷',
            'email' => 'Ali@Example.com',
            'subject' => 'مشاوره پروژه',
            'message' => 'سلام، می‌خواهم درباره طراحی سایت مشاوره بگیرم.',
            'captcha' => $this->captcha(),
        ], $over);
    }

    public function test_contact_page_renders(): void
    {
        $this->get('/contact')->assertOk()
            ->assertSee('۰۲۱-۲۲۴۵۱۹۱۲')
            ->assertSee('info@aron-soft.com')
            ->assertSee('بلوار ارتش')
            ->assertSee('openstreetmap.org');
    }

    public function test_contact_message_is_stored_and_admin_notified(): void
    {
        Mail::fake();

        $this
            ->get('/?utm_source=google&utm_medium=cpc&utm_campaign=spring');

        $this->postJson(route('contact.store'), $this->payload())
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $m = ContactMessage::firstOrFail();
        $this->assertSame('09121234567', $m->mobile);       // normalised
        $this->assertSame('ali@example.com', $m->email);
        $this->assertSame('new', $m->status);
        $this->assertSame('google', $m->utm_source);
        $this->assertSame('spring', $m->utm_campaign);

        Mail::assertSent(AdminNotificationMail::class, fn ($mail) => $mail->hasTo('admin@aron-soft.test'));
    }

    public function test_validation_messages_are_persian(): void
    {
        $res = $this
            ->postJson(route('contact.store'), $this->payload(['mobile' => '123', 'message' => 'کوتاه', 'name' => '']))
            ->assertStatus(422);

        $this->assertStringContainsString('موبایل', $res->json('errors.mobile.0'));
        $this->assertStringContainsString('الزامی', $res->json('errors.name.0'));
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_wrong_captcha_is_rejected(): void
    {
        $this
            ->postJson(route('contact.store'), $this->payload(['captcha' => 'nope']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('captcha');
    }

    public function test_honeypot_swallows_bots_silently(): void
    {
        Mail::fake();

        $this
            ->postJson(route('contact.store'), $this->payload(['website' => 'http://spam.example']))
            ->assertOk();

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_mail_failure_never_breaks_the_visitor(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this
            ->postJson(route('contact.store'), $this->payload())
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_callback_lead_requires_only_mobile_and_notifies_admin(): void
    {
        Mail::fake();

        $this->postJson(route('leads.store'), ['mobile' => '09351112233', 'request_type' => 'مشاوره سئو'])
            ->assertOk()->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('leads', ['mobile' => '09351112233', 'request_type' => 'مشاوره سئو', 'needs_followup' => 1]);
        Mail::assertSent(AdminNotificationMail::class);

        $this->postJson(route('leads.store'), ['mobile' => 'abc'])->assertStatus(422);
    }

    public function test_non_ajax_lead_flash_has_no_emoji(): void
    {
        $this->from('/')->post(route('leads.store'), ['mobile' => '09351112233'])
            ->assertRedirect('/')
            ->assertSessionHas('success', fn ($m) => ! preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $m));
    }
}
