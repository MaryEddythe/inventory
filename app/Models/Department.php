<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $connection = 'employee_db';
    protected $table = 'departments';
    protected $fillable = ['dept_no', 'department', 'description', 'last_updated', 'updated_by'];

    public $timestamps = false;

    const UPDATED_AT = 'last_updated';
}
