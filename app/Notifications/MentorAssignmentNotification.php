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
        return (new MailMessage)
            ->subject('List of Your Assigned Students')
            ->markdown('emails.assigned_students', [
                'instructor' => $this->instructor,
                'students' => $this->students
            ]);
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