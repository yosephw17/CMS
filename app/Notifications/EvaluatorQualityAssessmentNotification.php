<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class EvaluatorQualityAssessmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The instructor name being evaluated
     */
    public string $instructorName;

    /**
     * The evaluation URL
     */
    public string $evaluationUrl;

    /**
     * Create a new notification instance
     */
    public function __construct(
        public string $evaluatorName,
        string $instructorName,
        string $evaluationUrl
    ) {
        $this->instructorName = $instructorName;
        $this->evaluationUrl = $evaluationUrl;
    }

    /**
     * Get the notification's delivery channels
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Quality Assessment Request for {$this->instructorName}")
            ->greeting("Hello {$this->evaluatorName},")
            ->line("You have been requested to evaluate instructor: {$this->instructorName}")
            ->line("Please provide your honest feedback about the course quality.")
            ->action('Complete Evaluation', $this->evaluationUrl)
            ->line('Thank you for helping us improve our education quality!')
            ->salutation('Best regards,\nThe Quality Assurance Team');
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray(object $notifiable): array
    {
        return [
            'instructor' => $this->instructorName,
            'evaluation_url' => $this->evaluationUrl
        ];
    }
}