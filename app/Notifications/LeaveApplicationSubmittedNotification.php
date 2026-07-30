<?php

namespace App\Notifications;

use App\Models\EmployeeLeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveApplicationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public EmployeeLeaveApplication $application)
    {
    }

    public function via(object $notifiable): array
    {
        return filled($notifiable->email) ? ['database', 'mail'] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'employee_id' => $this->application->employee_id,
            'employee_name' => $this->application->employee?->full_name,
            'leave_type' => $this->application->leave_type,
            'headline' => 'New leave application awaiting HR review',
            'message' => $this->application->employee?->full_name . ' submitted a leave application that is now pending HR approval.',
            'step' => 'HR',
            'url' => route('leave-applications.index', ['application' => $this->application->id]),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New leave application awaiting HR review')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line($this->application->employee?->full_name . ' submitted a leave application.')
            ->line('Leave type: ' . $this->application->leave_type)
            ->line('The application is now pending HR review.')
            ->action('Review Leave Applications', route('leave-applications.index', ['application' => $this->application->id]));
    }
}
