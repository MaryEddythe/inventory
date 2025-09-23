<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_items';
    protected $primaryKey = 'no';

    protected $fillable = [
        'division',
        'enduser',
        'classification',
        'description',
        'serial_number',
        'property_number',
        'unit_price',
        'co_mooe',
        'date_acquired',
        'remarks'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'date_acquired' => 'date',
    ];
}