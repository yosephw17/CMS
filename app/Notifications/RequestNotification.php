<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RequestNotification extends Notification
{
    protected $signedUrl;

    public function __construct($signedUrl)
    {
        $this->signedUrl = $signedUrl;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Request Notification')
            ->line('You have a new request. Click the link below to choose your courses:')
            ->action('Choose Courses', $this->signedUrl)
            ->line('This link will expire in 24 hours.')
            ->line('Thank you!');
    }
}
