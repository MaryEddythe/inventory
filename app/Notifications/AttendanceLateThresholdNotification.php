<?php

namespace App\Notifications;

use App\Models\AttendanceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceLateThresholdNotification extends Notification
{
    use Queueable;

    public function __construct(
        public AttendanceRecord $record,
        public int $lateCount,
        public int $threshold,
        public string $headline,
        public string $message,
        public string $kind
    ) {
    }

    public function via(object $notifiable): array
    {
        return filled($notifiable->email) ? ['database', 'mail'] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'attendance_record_id' => $this->record->id,
            'employee_id' => $this->record->employee_id,
            'employee_name' => $this->record->employee?->full_name,
            'attendance_date' => optional($this->record->attendance_date)->toDateString(),
            'late_count' => $this->lateCount,
            'threshold' => $this->threshold,
            'kind' => $this->kind,
            'headline' => $this->headline,
            'message' => $this->message,
            'url' => route('attendance.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->headline)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line($this->message)
            ->line('Employee: ' . ($this->record->employee?->full_name ?? $this->record->employee_id))
            ->line('Late count this month: ' . $this->lateCount)
            ->action('Open Attendance Monitor', route('attendance.index'));
    }
}
