<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $primaryKey = 'emp_no';

    protected $table = 'inventory.employees';

    public function getRouteKeyName(): string
    {
        return 'emp_no';
    }

    protected $fillable = [
        'emp_no',
        'employee_id',

        // Legacy schema columns (what EmployeeController@store is writing)
        'firstname',
        'lastname',

        // Newer/alternate naming used elsewhere in the app
        'first_name',
        'last_name',

        'email',
        'division_id',
        'position',
        'employment_type',
        'leave_type',
        'Role',
        'dob',
        'drive_folder_id',
        'drive_folder_url',
        'drive',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public $timestamps = false;



    // Aliases for legacy column names in inventory.employees
    protected function setTableAttributeAliases(): void
    {
        // no-op (reserved for future)
    }

    // Accessors to map legacy columns to app field names
    public function getFirstNameAttribute(): ?string
    {
        return $this->attributes['first_name'] ?? ($this->attributes['firstname'] ?? null);
    }

    public function getLastNameAttribute(): ?string
    {
        return $this->attributes['last_name'] ?? ($this->attributes['lastname'] ?? null);
    }

    public function getDobAttribute($value): ?\Illuminate\Support\Carbon
    {
        return $value ? $this->asDateTime($value) : null;
    }

    public function getFullNameAttribute(): string
    {
        return ($this->first_name ?? 'N/A') . ' ' . ($this->last_name ?? 'N/A');
    }


    public static function generateEmployeeId(): string
    {
        $latest = self::latest('id')->first();

        $number = $latest
            ? ((int) filter_var($latest->employee_id, FILTER_SANITIZE_NUMBER_INT)) + 1
            : 1;

        return 'EMP-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department', 'dept_no');
    }

    // Division is stored as inventory.departments using division_id => dept_no.
    public function division()
    {
        return $this->belongsTo(Department::class, 'division_id', 'dept_no');
    }









    public function files()

    {
        return $this->hasMany(EmployeeFile::class);
    }

    public function leaveBenefits()
    {
        return $this->hasMany(EmployeeLeaveBenefit::class, 'emp_no', 'emp_no');
    }

    public function leaveHistory()
    {
        return $this->hasMany(EmployeeLeaveHistory::class, 'emp_no', 'emp_no');
    }
    
    // Add this accessor
    public function getPositionAttribute(): ?string
    {
        return $this->attributes['Role'] ?? null;
    }
    // (removed duplicate division() relation to fix redeclare error)
}

