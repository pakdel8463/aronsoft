<?php

namespace App\Mail;

use App\Models\Newsletter;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewsletterConfirmMail extends Mailable
{
    public function __construct(public Newsletter $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'تأیید عضویت در خبرنامه آرون سافت');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.newsletter-confirm', with: [
            'confirmUrl' => route('newsletter.confirm', $this->subscriber->token),
            'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscriber->token),
        ]);
    }
}
