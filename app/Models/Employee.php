<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Primary key is actually `emp_no` in inventory.employees
    // Keeping this as emp_no ensures Eloquent updates/relations are consistent.
    protected $primaryKey = 'emp_no';

    // This model is intended to write/read from the legacy schema: inventory.employees
    protected $table = 'inventory.employees';

    public function getRouteKeyName(): string
    {
        return 'emp_no';
    }

    // inventory.employees columns (legacy schema):
    // emp_no, lastname, firstname, department, descr, Role, dob, status, updated_at
    // We map/alias them to the field names expected by the rest of the app.
    protected $fillable = [
        'emp_no',
        'employee_id',

        // Legacy schema columns (what EmployeeController@store is writing)
        'firstname',
        'lastname',

        // Legacy columns used for division/role display
        'department', // inventory.departments.dept_no
        'Role', // legacy column name for position/title

        // Newer/alternate naming used elsewhere in the app
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
        // Stores all Google Drive links created for the employee on creation (JSON string)
        'drive',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    // Your legacy table `inventory.employees` does not have created_at.
    // Disable timestamps so Eloquent only writes updated_at.
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
        // inventory.employees.department stores inventory.departments.dept_no
        return $this->belongsTo(Department::class, 'department', 'dept_no');
    }

    public function departmentInfo()
    {
        // Legacy name expected by InventoryItemController::searchEmployees()
        // Map to the employee's division record.
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function division()
    {
        return $this->belongsTo(Department::class, 'department', 'dept_no');
    }


    public function files()
    {
        return $this->hasMany(EmployeeFile::class);
    }

    public function leaveBenefits()
    {
        // employee_leave_benefits table uses `emp_no` to reference inventory.employees.emp_no
        return $this->hasMany(EmployeeLeaveBenefit::class, 'emp_no', 'emp_no');
    }

    public function leaveHistory()
    {
        // employee_leave_history table uses `emp_no` to reference inventory.employees.emp_no
        return $this->hasMany(EmployeeLeaveHistory::class, 'emp_no', 'emp_no');
    }
    public function getPositionAttribute(): ?string
    {
        return $this->attributes['Role'] ?? null;
    }

}

