<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icm extends Model
{
protected $connection = 'mysql';
    protected $table = 'icm';
    protected $primaryKey = 'id';
    public $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'inventory_item_id',
        'icm_no',
        'division',
        'requesting_personnel',
        'problem_description',
        'icm_type',
        'priority',
        'classification',
        'property_number',
        'serial_number',
        'brand_model',
        'hardware_software',
        'open_date',
        'open_time',
        'close_date',
        'close_time',
        'icm_findings',
        'actions_taken',
        'icm_recommendations',
        'date_conducted',
        'time_started',
        'time_ended',
    ];

    protected $casts = [
        'open_date' => 'date',
        'close_date' => 'date',
        'date_conducted' => 'date',
    ];

    public function inventoryItem()
{
    return $this->belongsTo(\App\Models\InventoryItem::class, 'inventory_item_id', 'no');
}
}