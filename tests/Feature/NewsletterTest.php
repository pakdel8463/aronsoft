<?php

namespace Tests\Feature;

use App\Mail\NewsletterConfirmMail;
use App\Models\Newsletter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    private function subscribe(string $email = 'Reader@Example.com')
    {
        return $this
            ->postJson(route('newsletter.subscribe'), ['email' => $email, 'captcha' => $this->captcha()]);
    }

    public function test_subscribe_creates_inactive_row_and_sends_confirmation(): void
    {
        Mail::fake();

        $this->subscribe()->assertOk();

        $n = Newsletter::firstOrFail();
        $this->assertSame('reader@example.com', $n->email);
        $this->assertFalse($n->is_active);
        $this->assertNull($n->confirmed_at);
        $this->assertGreaterThanOrEqual(32, strlen($n->token));

        Mail::assertSent(NewsletterConfirmMail::class, fn ($m) => $m->hasTo('reader@example.com')
            && str_contains($m->content()->with['confirmUrl'], $n->token));
    }

    public function test_confirm_activates_and_unsubscribe_deactivates(): void
    {
        Mail::fake();
        $this->subscribe();
        $n = Newsletter::firstOrFail();

        $this->get(route('newsletter.confirm', $n->token))->assertOk()->assertSee('عضویت شما تأیید شد');
        $n->refresh();
        $this->assertTrue($n->is_active);
        $this->assertNotNull($n->confirmed_at);

        $this->get(route('newsletter.unsubscribe', $n->token))->assertOk()->assertSee('عضویت شما لغو شد');
        $n->refresh();
        $this->assertFalse($n->is_active);
        $this->assertNotNull($n->unsubscribed_at);
    }

    public function test_invalid_tokens_are_404(): void
    {
        $this->get('/newsletter/confirm/'.str_repeat('x', 40))->assertNotFound();
        $this->get('/newsletter/unsubscribe/short')->assertNotFound();
    }

    public function test_active_subscriber_is_not_re_mailed_and_inactive_one_gets_a_new_confirmation(): void
    {
        Mail::fake();
        $this->subscribe();
        $n = Newsletter::firstOrFail();
        $n->update(['is_active' => true]);

        $this->subscribe()->assertOk()->assertJson(['message' => 'این ایمیل قبلاً در خبرنامه عضو شده است.']);
        Mail::assertSent(NewsletterConfirmMail::class, 1);

        $n->update(['is_active' => false]);
        $this->subscribe()->assertOk();
        Mail::assertSent(NewsletterConfirmMail::class, 2);
        $this->assertDatabaseCount('newsletters', 1);
    }

    public function test_mail_failure_is_reported_honestly_for_opt_in(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));
        $this->subscribe()->assertStatus(503);
    }

    public function test_captcha_and_email_are_validated(): void
    {
        $this
            ->postJson(route('newsletter.subscribe'), ['email' => 'bad', 'captcha' => 'zzz'])
            ->assertStatus(422)->assertJsonValidationErrors(['email', 'captcha']);
    }
}
