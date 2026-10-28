<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** ایمیل متنی فرم‌ساز (تأیید برای کاربر یا اطلاع به آدرس‌های اضافه‌ی فرم). */
class FormConfirmationMail extends Mailable
{
    public function __construct(public string $subjectLine, public string $body, public ?string $url = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.form-message');
    }
}
