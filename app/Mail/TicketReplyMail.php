<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** اعلان به مشتری: پاسخ پشتیبانی به تیکت. */
class TicketReplyMail extends Mailable
{
    public function __construct(public Ticket $ticket, public TicketMessage $ticketMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "پاسخ جدید به تیکت {$this->ticket->number}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-reply', with: [
            'url' => route('tickets.show', $this->ticket),
            'excerpt' => \Illuminate\Support\Str::limit(trim($this->ticketMessage->body), 600),
        ]);
    }
}
