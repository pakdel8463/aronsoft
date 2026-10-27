<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** پیش‌فاکتور آنی برای مشتری (قالب: resources/views/emails/invoice.blade.php) */
class InvoiceSent extends Mailable
{
    public function __construct(public string $url, public string $projectTitle = 'پیش‌فاکتور آرون سافت') {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'پیش‌فاکتور آنی: '.$this->projectTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice', with: ['url' => $this->url]);
    }
}
