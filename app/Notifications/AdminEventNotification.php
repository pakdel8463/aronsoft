<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class AdminEventNotification extends Notification
{
    use Queueable;
    public function __construct(public string $subject, public string $heading, public ?string $url, public string $event) {}
    public function via(object $notifiable): array { return ['database']; }
    public function toArray(object $notifiable): array { return ['subject' => $this->subject, 'heading' => $this->heading, 'url' => $this->url, 'event' => $this->event]; }
}
