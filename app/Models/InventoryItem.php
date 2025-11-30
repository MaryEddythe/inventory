<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InventoryItem extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'inventory_items';
    protected $primaryKey = 'no';
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'division',
        'enduser',
        'emp_no',
        'classification',
        'description',
        'serial_number',
        'property_number',
        'unit_price',
        'co_mooe',
        'date_acquired',
        'remarks',
        'status',
        'condition',
        'system_boot_up',
        'hardware',
        'performance',
        'cables_connections',
        'peripherals',
        'recommendation',
        'date_conducted',
        'time_started',
        'time_ended',
        'x',
        'updated_by',
        'updated_at',
    ];

    protected $dates = [
        'date_acquired'
    ];

    protected $casts = [
        'date_acquired' => 'date',
        'unit_price' => 'decimal:2',
        'system_boot_up' => 'boolean',
        'hardware' => 'boolean',
        'performance' => 'boolean',
        'cables_connections' => 'boolean',
        'peripherals' => 'boolean',
        'date_conducted' => 'date',
        'updated_at' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    public function scopeActive($query)
    {
        return $query->where('x', '!=', 'INACTIVE');
    }
}