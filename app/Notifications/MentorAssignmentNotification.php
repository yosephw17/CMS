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
        // Create the email content
        $mailMessage = (new MailMessage)
            ->subject('List of Your Assigned Students')
            ->line('Dear ' . $this->instructor->name . ',')
            ->line('Here is the list of students assigned to you as their mentor:')
            ->line('');

        // Add each student's details to the email
        foreach ($this->students as $student) {
            $mailMessage->line('- ' . $student->full_name . ' (' . $student->phone_number . ')')
                        ->line('  Location: ' . $student->location)
                        ->line('  Hosting Company: ' . $student->hosting_company)
                        ->line(''); // Add a blank line between students
        }

        $mailMessage->line('')
            ->line('Thank you for your support!');

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