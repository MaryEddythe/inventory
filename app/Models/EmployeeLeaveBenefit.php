<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBenefit extends Model
{
    protected $fillable = [
        'employee_id',
        'name',
        'division',
        'position',
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
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
