<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Generic "something arrived on the site" mail for the site owner. */
class AdminNotificationMail extends Mailable
{
    /**
     * @param  array<string, string|null>  $rows  label => value
     */
    public function __construct(
        public string $subjectLine,
        public string $heading,
        public array $rows = [],
        public ?string $url = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-notification');
    }
}
