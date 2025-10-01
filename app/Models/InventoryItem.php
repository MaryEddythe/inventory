<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InventoryItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_items';
    protected $primaryKey = 'no';
    public $timestamps = true;
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
        'x'
    ];

    protected $dates = [
        'date_acquired',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'date_acquired' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'unit_price' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($item) {
            $item->status = $item->calculateStatus();
        });
    }

    public function calculateStatus()
    {
        $dateAcquired = Carbon::parse($this->date_acquired);
        $yearsSinceAcquired = $dateAcquired->diffInYears(Carbon::now());
        
        return $yearsSinceAcquired <= 5 ? 'NEW' : 'FOR REPLACEMENT';
    }

    public function scopeActive($query)
    {
        return $query->where('x', 'active');
    }
}