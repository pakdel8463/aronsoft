<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderReceivedMail extends Mailable
{
    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'درخواست شما ثبت شد؛ کد پیگیری '.$this->order->code);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-received', with: ['trackUrl' => $this->order->publicUrl()]);
    }
}
