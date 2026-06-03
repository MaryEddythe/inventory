<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Employees table is now in the inventory database (same connection as inventory_items)
    protected $table = 'employees';
    protected $fillable = ['emp_no', 'lastname', 'firstname', 'department', 'descr', 'Role', 'dob', 'status', 'updated_at'];


    public $timestamps = false; // Assuming updated_at is used

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    // Relationship to get department name
    public function departmentInfo()
    {
        return $this->belongsTo(Department::class, 'department', 'dept_no');
    }
}
