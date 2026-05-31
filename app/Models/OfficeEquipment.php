<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeEquipment extends Model
{
    protected $table = 'office_equipments';

    protected $fillable = [
        'article',
        'description',
        'property_number',
        'unit_value',
        'date_acquired',
        'remarks',
    ];

    protected $casts = [
        'date_acquired' => 'date',
        'unit_value' => 'decimal:2',
    ];
}
