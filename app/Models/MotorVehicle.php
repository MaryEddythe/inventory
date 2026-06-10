<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotorVehicle extends Model
{
    protected $table = 'motor_vehicles';

    protected $fillable = [
        'article',
        'description',
        'property_number',
        'unit_value',
        'date_acquired',
'remarks',
        'co_mooe',
    ];

    protected $casts = [
        'date_acquired' => 'date',
        'unit_value' => 'decimal:2',
    ];
}
