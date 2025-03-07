<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Instructor;
use Illuminate\Support\Collection;

class MentorAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $instructor;
    protected $students;

    /**
     * Create a new notification instance.
     *
     * @param Instructor $instructor
     * @param Collection $students
     */
    public function __construct(Instructor $instructor, Collection $students)
    {
        $this->instructor = $instructor;
        $this->students = $students;
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
    // Create the email content with HTML formatting
    $mailMessage = (new MailMessage)
        ->subject('List of Your Assigned Students')
        ->greeting('Dear ' . $this->instructor->name . ',')
        ->line('Here is the list of students assigned to you as their mentor:')
        ->line('');

    // Add each student's details to the email
    foreach ($this->students as $student) {
        $mailMessage->line(
            "<strong>Name:</strong> " . $student->full_name . "<br>" .
            "<strong>Phone:</strong> " . $student->phone_number . "<br>" .
            "<strong>Location:</strong> " . $student->location . "<br>" .
            "<strong>Hosting Company:</strong> " . $student->hosting_company
        )
        ->line(''); // Add a blank line between students
    }

    $mailMessage->line('')
        ->line('Thank you for your support!')
        ->salutation('Best regards,<br>Your Team');

    return $mailMessage;
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