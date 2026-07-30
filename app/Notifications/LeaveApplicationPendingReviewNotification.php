<?php

namespace App\Notifications;

use App\Models\EmployeeLeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveApplicationPendingReviewNotification extends Notification
{
    use Queueable;

    public function __construct(
        public EmployeeLeaveApplication $application,
        public string $stepLabel,
        public string $headline,
        public string $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return filled($notifiable->email)
            ? ['database', 'mail']
            : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'employee_id' => $this->application->employee_id,
            'employee_name' => $this->application->employee?->full_name,
            'leave_type' => $this->application->leave_type,
            'step' => $this->stepLabel,
            'headline' => $this->headline,
            'message' => $this->message,
            'url' => route('leave-applications.index', ['application' => $this->application->id]),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->headline)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line($this->message)
            ->line('Leave type: ' . $this->application->leave_type)
            ->line('Step: ' . $this->stepLabel)
            ->action('View Leave Applications', route('leave-applications.index', ['application' => $this->application->id]));
    }
}
