<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'emp_no';

    public function getRouteKeyName(): string
    {
        return 'emp_no';
    }

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'division_id',
        'position',
        'employment_type',
        'leave_type',
        'dob',
        'drive_folder_id',
        'drive_folder_url',
    ];

    protected $casts = [
        'dob' => 'date',
    ];


    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public static function generateEmployeeId(): string
    {
        $latest = self::latest('id')->first();

        $number = $latest
            ? ((int) filter_var($latest->employee_id, FILTER_SANITIZE_NUMBER_INT)) + 1
            : 1;

        return 'EMP-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    public function departmentInfo()
    {
        // Legacy name expected by InventoryItemController::searchEmployees()
        // Map to the employee's division record.
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }


    public function files()
    {
        return $this->hasMany(EmployeeFile::class);
    }

    public function leaveBenefits()
    {
        return $this->hasMany(EmployeeLeaveBenefit::class);
    }

    public function leaveHistory()
    {
        return $this->hasMany(EmployeeLeaveHistory::class);
    }

}
