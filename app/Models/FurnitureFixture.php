<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FurnitureFixture extends Model
{
    protected $table = 'furniture_fixtures';

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
        'unit_value' => 'decimal:2',
        'date_acquired' => 'date',
    ];
}
