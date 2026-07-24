<?php

namespace App\Observers;

use App\Models\EmployeeLeaveApplication;
use App\Models\User;
use App\Notifications\LeaveApplicationSignedNotification;

class EmployeeLeaveApplicationObserver
{
    public function updated(EmployeeLeaveApplication $application): void
    {
        $stepUpdates = [
            [
                'signed_at' => 'hr_signed_at',
                'step_label' => 'HR',
                'headline' => 'Your leave application has been signed by HR',
                'message' => 'HR has signed your leave application and it is now moving to the Division Chief for the next approval step.',
            ],
            [
                'signed_at' => 'division_chief_signed_at',
                'step_label' => 'Division Chief',
                'headline' => 'Your leave application has been signed by the Division Chief',
                'message' => 'The Division Chief has signed your leave application and it is now moving to the Regional Director for review.',
            ],
            [
                'signed_at' => 'regional_director_signed_at',
                'step_label' => 'Regional Director',
                'headline' => 'Your leave application has been approved',
                'message' => 'The Regional Director has signed your leave application. Your leave request is now fully approved.',
            ],
        ];

        $recipient = $this->recipientFor($application);

        if (! $recipient) {
            return;
        }

        foreach ($stepUpdates as $stepUpdate) {
            if ($this->stepWasJustSigned($application, $stepUpdate['signed_at'])) {
                $recipient->notify(new LeaveApplicationSignedNotification(
                    application: $application,
                    stepLabel: $stepUpdate['step_label'],
                    headline: $stepUpdate['headline'],
                    message: $stepUpdate['message'],
                ));
            }
        }
    }

    protected function recipientFor(EmployeeLeaveApplication $application): ?User
    {
        $employee = $application->employee;

        if ($employee?->relationLoaded('user') && $employee->user) {
            return $employee->user;
        }

        return User::query()
            ->where('emp_no', $application->employee_id)
            ->first();
    }

    protected function stepWasJustSigned(EmployeeLeaveApplication $application, string $signedAtField): bool
    {
        $originalSignedAt = $application->getOriginal($signedAtField);

        return blank($originalSignedAt) && filled($application->{$signedAtField});
    }
}
