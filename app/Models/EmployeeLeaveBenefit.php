<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBenefit extends Model
{
    protected $fillable = [
        'emp_no',
        'name',
        'departments',
        'role',
        'employment_type',
        'credit_type',
        'start_date',
        'end_date',
        'credit_hours',
        'hours_used',
        'hours_remaining',
        'status',
        'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'credit_hours' => 'integer',
        'hours_used' => 'integer',
        'hours_remaining' => 'integer',
    ];

    /**
     * Get the employee that owns the leave benefit.
     *
     * employee_leave_benefits.emp_no  -> inventory.employees.emp_no
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_no', 'emp_no');
    }

    /**
     * Backward-compatibility alias for any legacy code expecting `employee_emp_no`.
     * (Prevents SQL errors when some relationship usage still relies on the default FK name.)
     */
    public function employeeEmpNo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_no', 'emp_no');
    }

    /**
     * Prevent legacy/automatic FK name fallback (`employee_emp_no`) from causing SQL errors.
     */
    public function employeeLeaveHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeLeaveHistory::class, 'leave_benefit_id');
    }
}
