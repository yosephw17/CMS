<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstructorCourseAuditNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $instructorName;
    protected $auditUrl;

    public function __construct(string $instructorName, string $auditUrl)
    {
        $this->instructorName = $instructorName;
        $this->auditUrl = $auditUrl;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Course Audit Form - Quality Assurance')
            ->greeting("Dear {$this->instructorName},")
            ->line('Quality Assurance & Enhancement Office')
            ->line('')
            ->line('This form is to be filled out by the course instructor for the course audit.')
            ->line('Please complete all fields accurately using this link:')
            ->action('Access Course Audit Form', $this->auditUrl);
    }
}