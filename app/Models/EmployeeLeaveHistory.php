<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveHistory extends Model
{
    protected $table = 'employee_leave_history';

    protected $fillable = [
        'employee_id',
        'leave_benefit_id',
        'credit_type',
        'credits_added',
        'hours_used',
        'hours_remaining',
        'remarks',
    ];

    protected $casts = [
        'credits_added' => 'integer',
        'hours_used' => 'integer',
        'hours_remaining' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveBenefit(): BelongsTo
    {
        return $this->belongsTo(EmployeeLeaveBenefit::class, 'leave_benefit_id');
    }
}
