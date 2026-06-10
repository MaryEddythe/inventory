<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherPpe extends Model
{
    protected $table = 'other_ppes';

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
