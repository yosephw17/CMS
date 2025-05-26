<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendPasswordNotification extends Notification
{
    use Queueable;

    protected $password;

    /**
     * Create a new notification instance.
     *
     * @param string $password
     */
    public function __construct($password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
{
    $frontendUrl = rtrim(env('FRONTEND_URL'), '/'); // Remove trailing slash if exists

    return (new MailMessage)
                ->subject('Your Account Password')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your account has been created successfully.')
                ->line('Your password is: **' . $this->password . '**')
                ->line('Please change your password after logging in for security.')
                ->action('Login Now', $frontendUrl . '/login')
                ->line('Thank you for using our application!');
}

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}