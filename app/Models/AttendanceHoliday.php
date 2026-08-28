<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_date',
        'title',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'holiday_date' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
