<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'emp_no';

    // This model is intended to write/read from the legacy schema: inventory.employees
    protected $table = 'inventory.employees';

    public function getRouteKeyName(): string
    {
        return 'emp_no';
    }

    protected $fillable = [
        'emp_no',
        'employee_id',

        'firstname',
        'lastname',

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
        'drive',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public $timestamps = false;



    protected function setTableAttributeAliases(): void
    {
        // no-op (reserved for future)
    }

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
    public function departmentInfo()
    {
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

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'emp_no', 'emp_no');
    }

    public function leaveBenefits()
    {
        // employee_leave_benefits.emp_no -> inventory.employees.emp_no
        return $this->hasMany(EmployeeLeaveBenefit::class, 'emp_no', 'emp_no');
    }

    public function leaveHistory()
    {
        return $this->hasMany(EmployeeLeaveHistory::class, 'employee_id', 'emp_no');
    }

    public function leaveApplications()
    {
        return $this->hasMany(EmployeeLeaveApplication::class, 'employee_id', 'emp_no');
    }

}
