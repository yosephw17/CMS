<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentEvaluationNotification extends Notification
{
    use Queueable;

    protected $studentName;
    protected $evaluationUrl;
    protected $expiresAt;

    public function __construct($studentName, $evaluationUrl, $expiresAt)
    {
        $this->studentName = $studentName;
        $this->evaluationUrl = $evaluationUrl;
        $this->expiresAt = $expiresAt;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Course Evaluation Request')
            ->greeting("Hello {$this->studentName},")
            ->line('You have been requested to evaluate your instructor.')
            ->line('Please click the button below to complete the evaluation form:')
            ->action('Complete Evaluation', $this->evaluationUrl)
            ->line('Thank you for your feedback!');
    }
}