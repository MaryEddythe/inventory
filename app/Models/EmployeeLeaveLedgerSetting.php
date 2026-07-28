<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveLedgerSetting extends Model
{
    protected $fillable = [
        'emp_no',
        'first_day_of_service',
        'opening_balance_date',
        'opening_vacation_balance',
        'opening_sick_balance',
        'remarks',
    ];

    protected $casts = [
        'first_day_of_service' => 'date',
        'opening_balance_date' => 'date',
        'opening_vacation_balance' => 'decimal:3',
        'opening_sick_balance' => 'decimal:3',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_no', 'emp_no');
    }
}
