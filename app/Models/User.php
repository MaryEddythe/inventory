<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'emp_no',
        'employee',
        'signature_path',
        'profile_image',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Link user to the legacy employee record using emp_no.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_no', 'emp_no');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function sidebarItems(): BelongsToMany
    {
        return $this->belongsToMany(SidebarItem::class, 'user_sidebar_item')->orderBy('sort_order');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) ($this->role?->is_superadmin || $this->role?->slug === 'superadmin');
    }

    public function isHr(): bool
    {
        return (bool) ($this->role?->slug === 'hr');
    }

    public function canAccessSidebarItem(SidebarItem $item): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $directIds = $this->sidebarItems()->pluck('sidebar_items.id')->all();

        if (count($directIds) > 0) {
            return in_array($item->id, $directIds, true);
        }

        return $this->role
            ? $this->role->sidebarItems->contains('id', $item->id)
            : false;
    }

    public function sidebarNavigation(): Collection
    {
        $items = SidebarItem::query()
            ->with([
                'children' => fn ($query) => $query->ordered()->with('children'),
            ])
            ->ordered()
            ->get();

        $allowedIds = $this->isSuperAdmin()
            ? $items->pluck('id')->all()
            : (
                $this->sidebarItems()->exists()
                    ? $this->sidebarItems()->pluck('sidebar_items.id')->all()
                    : ($this->role?->sidebarItems()->pluck('sidebar_items.id')->all() ?? [])
            );

        return $items
            ->whereNull('parent_id')
            ->map(fn (SidebarItem $item) => $this->mapSidebarItem($item, $allowedIds))
            ->filter()
            ->values();
    }

    public function defaultLandingRouteName(): string
    {
        if ($this->isSuperAdmin()) {
            return 'inventory.dashboard';
        }

        foreach ($this->sidebarNavigation() as $item) {
            $routeName = $this->firstRouteNameFromNode($item);

            if ($routeName) {
                return $routeName;
            }
        }

        return 'inventory.dashboard';
    }

    protected function mapSidebarItem(SidebarItem $item, array $allowedIds): ?array
    {
        if ($item->key === 'leave-ledgers' && ! $this->isSuperAdmin() && (int) $this->role_id !== 4) {
            return null;
        }

        $children = $item->children
            ->map(fn (SidebarItem $child) => $this->mapSidebarItem($child, $allowedIds))
            ->filter()
            ->values();

        $visible = ($this->isSuperAdmin()
            || in_array($item->id, $allowedIds, true)
            || $children->isNotEmpty())
            && ($item->route_name || $children->isNotEmpty());

        if (! $visible) {
            return null;
        }

        $routeName = $item->route_name;
        $routePattern = $item->route_pattern ?: $routeName;

        return [
            'id' => $item->id,
            'key' => $item->key,
            'label' => $item->label,
            'icon' => $item->icon,
            'route_name' => $routeName,
            'route_pattern' => $routePattern,
            'url' => $routeName && ! Str::contains($routeName, '*') ? route($routeName) : null,
            'active' => $routePattern ? request()->routeIs($routePattern) : false,
            'children' => $children,
        ];
    }

    protected function firstRouteNameFromNode(array $item): ?string
    {
        if (! empty($item['route_name']) && ! Str::contains($item['route_name'], '*')) {
            return $item['route_name'];
        }

        foreach ($item['children'] as $child) {
            $routeName = $this->firstRouteNameFromNode($child);

            if ($routeName) {
                return $routeName;
            }
        }

        return null;
    }
}
