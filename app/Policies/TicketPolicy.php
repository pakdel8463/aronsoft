<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;

/**
 * مالک تیکت (مشتری) یا کارمند دارای دسترسی tickets.manage.
 * توجه: Gate::before در AuthServiceProvider سوپرکاربر را همه‌جا مجاز می‌کند.
 */
class TicketPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasPermission('tickets.manage');
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id || $this->manage($user);
    }

    /** پاسخ مشتری (فقط مالک و تا وقتی تیکت باز است). */
    public function reply(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id && ! $ticket->isClosed();
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id && ! $ticket->isClosed();
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id && $ticket->canReopen();
    }

    public function rate(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id && $ticket->isClosed() && $ticket->rated_at === null;
    }

    /** دانلود پیوست: مالک (بدون یادداشت‌های داخلی) یا مدیر تیکت. */
    public function download(User $user, Ticket $ticket, TicketAttachment $attachment): bool
    {
        if ($attachment->ticket_id !== $ticket->id) {
            return false;
        }
        if ($this->manage($user)) {
            return true;
        }

        return $ticket->user_id === $user->id && ! $attachment->message?->isNote();
    }
}
