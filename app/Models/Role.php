<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_superadmin',
    ];

    protected function casts(): array
    {
        return [
            'is_superadmin' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sidebarItems(): BelongsToMany
    {
        return $this->belongsToMany(SidebarItem::class, 'role_sidebar_item')->orderBy('sort_order');
    }
}
