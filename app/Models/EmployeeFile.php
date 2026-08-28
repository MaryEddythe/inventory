<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFile extends Model
{
    protected $fillable = [
        'emp_no',
        'file_type',
        'file_name',
        'file_url',
        'file_id',
    ];
}

