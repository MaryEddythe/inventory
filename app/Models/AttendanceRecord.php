<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'schedule_type',
        'attendance_date',
        'status',
        'check_in_at',
        'check_out_at',
        'minutes_late',
        'notes',
        'late_warning_sent_at',
        'memo_flagged_at',
        'absence_follow_up_sent_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'late_warning_sent_at' => 'datetime',
        'memo_flagged_at' => 'datetime',
        'absence_follow_up_sent_at' => 'datetime',
        'minutes_late' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_no');
    }

    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }
}
