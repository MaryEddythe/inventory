<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;
use App\States\LeaveApplication\LeaveApplicationState;

class EmployeeLeaveApplication extends Model
{
    use HasStates;
    use LogsActivity;

    protected $table = 'employees_leave_history';

    protected $fillable = [
        'employee_id',
        'leave_type',
        'date_from',
        'date_to',
        'reason',
        'applicant_signature_path',
        'applicant_signed_at',
        'status',
        'current_step',
        'hr_signed_by',
        'hr_signed_at',
        'hr_signature_path',
        'division_chief_signed_by',
        'division_chief_signed_at',
        'division_chief_signature_path',
        'regional_director_signed_by',
        'regional_director_signed_at',
        'regional_director_signature_path',
        'ip_address',
        'user_agent',
        'signing_notes',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'applicant_signed_at' => 'datetime',
        'hr_signed_at' => 'datetime',
        'division_chief_signed_at' => 'datetime',
        'regional_director_signed_at' => 'datetime',
        'status' => LeaveApplicationState::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_no');
    }

    public function hrSigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_signed_by');
    }

    public function divisionChiefSigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'division_chief_signed_by');
    }

    public function regionalDirectorSigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'regional_director_signed_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
