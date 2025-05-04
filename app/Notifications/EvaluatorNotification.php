<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Evaluator;

class EvaluatorNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $evaluatorName,
        public string $instructorName,
        public string $evaluationUrl,
        public string $evaluatorType,
        public string $expiresAt
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $role = match($this->evaluatorType) {
            Evaluator::TYPE_STUDENT => 'student',
            Evaluator::TYPE_INSTRUCTOR => 'fellow instructor',
            Evaluator::TYPE_DEAN => 'dean',
            default => 'evaluator'
        };

        return (new MailMessage)
            ->subject("Instructor Evaluation Request ({$this->evaluatorType})")
            ->greeting("Hello {$this->evaluatorName},")
            ->line("As a {$role}, you have been requested to evaluate {$this->instructorName}.")
            ->line('Please click the button below to complete the evaluation form:')
            ->action('Complete Evaluation', $this->evaluationUrl)
            ->line('Thank you for your feedback!');
    }
}