<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MilitaryPoliceSecurityEquipment extends Model
{
    protected $table = 'military_police_security_equipments';

    protected $fillable = [
        'article',
        'description',
        'property_number',
        'unit_value',
        'co_mooe',
        'date_acquired',
        'remarks',
    ];

    protected $casts = [
        'date_acquired' => 'date',
        'unit_value' => 'decimal:2',
    ];
}
