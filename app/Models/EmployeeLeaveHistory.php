<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveHistory extends Model
{
    protected $table = 'employee_leave_history';

    protected $fillable = [
        'emp_no',
        'employee_id',
        'leave_benefit_id',
        'credit_type',
        'credits_added',
        'hours_used',
        'hours_remaining',
        'remarks',
        'location',
    ];

    protected $casts = [
        'credits_added' => 'integer',
        'hours_used' => 'integer',
        'hours_remaining' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_no', 'emp_no');
    }

    public function leaveBenefit(): BelongsTo
    {
        return $this->belongsTo(EmployeeLeaveBenefit::class, 'leave_benefit_id');
    }
}
