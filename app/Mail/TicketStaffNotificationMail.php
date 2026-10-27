<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** اعلان به پشتیبانی: تیکت جدید یا پاسخ مشتری. */
class TicketStaffNotificationMail extends Mailable
{
    public function __construct(public Ticket $ticket, public TicketMessage $ticketMessage, public bool $isNew = false) {}

    public function envelope(): Envelope
    {
        $prefix = $this->isNew ? 'تیکت جدید' : 'پاسخ مشتری';

        return new Envelope(subject: "{$prefix} {$this->ticket->number} — {$this->ticket->subject}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-staff', with: [
            'url' => route('panel.tickets.show', $this->ticket),
            'customer' => $this->ticket->user,
            'excerpt' => \Illuminate\Support\Str::limit(trim($this->ticketMessage->body), 600),
        ]);
    }
}
