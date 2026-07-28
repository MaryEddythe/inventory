<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    use HasFactory;

    // Departments table is stored in `inventory.departments`
    protected $table = 'inventory.departments';



    protected $fillable = ['dept_no', 'department', 'description', 'division_chief_role_id', 'last_updated', 'updated_by'];

    public $timestamps = false;

    const UPDATED_AT = 'last_updated';

    public function divisionChiefRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'division_chief_role_id');
    }
}
