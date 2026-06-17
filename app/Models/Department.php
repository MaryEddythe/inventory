<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    // Departments table is stored in `inventory.departments`
    protected $table = 'inventory.departments';



    protected $fillable = ['dept_no', 'department', 'description', 'last_updated', 'updated_by'];

    public $timestamps = false;

    const UPDATED_AT = 'last_updated';
}
