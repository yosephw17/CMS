<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RequestNotification extends Notification
{
    protected $instructor;

    public function __construct($instructor)
    {
        $this->instructor = $instructor;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('New Request Notification')
                    ->line('You have a new request.')
                    ->action('View Request', url('/'))
                    ->line('Thank you!');
    }
}
