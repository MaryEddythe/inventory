<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    // Departments table is now in the inventory database (same connection as inventory_items)
    protected $table = 'departments';

    protected $fillable = ['dept_no', 'department', 'description', 'last_updated', 'updated_by'];

    public $timestamps = false;

    const UPDATED_AT = 'last_updated';
    public function division()
{
    return $this->belongsTo(Department::class, 'department', 'dept_no');
}
}
