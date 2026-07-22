<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SidebarItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SidebarAccessSeeder extends Seeder
{
    public function run(): void
    {
        $inventoryConfig = config('inventory');
        $rolesConfig = $inventoryConfig['roles'] ?? [];
        $sidebarItemsConfig = $inventoryConfig['sidebar_items'] ?? [];

        DB::transaction(function () use ($inventoryConfig, $rolesConfig, $sidebarItemsConfig) {
            $rolesBySlug = [];
            foreach ($rolesConfig as $roleConfig) {
                $role = Role::updateOrCreate(
                    ['slug' => $roleConfig['slug']],
                    [
                        'name' => $roleConfig['name'],
                        'is_superadmin' => (bool) ($roleConfig['is_superadmin'] ?? false),
                    ]
                );

                $rolesBySlug[$role->slug] = $role;
            }

            $itemsByKey = [];
            foreach ($sidebarItemsConfig as $itemConfig) {
                $parentId = null;
                if (! empty($itemConfig['parent_key']) && isset($itemsByKey[$itemConfig['parent_key']])) {
                    $parentId = $itemsByKey[$itemConfig['parent_key']]->id;
                }

                $item = SidebarItem::updateOrCreate(
                    ['key' => $itemConfig['key']],
                    [
                        'label' => $itemConfig['label'],
                        'route_name' => $itemConfig['route_name'] ?? null,
                        'route_pattern' => $itemConfig['route_pattern'] ?? $itemConfig['route_name'] ?? null,
                        'icon' => $itemConfig['icon'] ?? null,
                        'parent_id' => $parentId,
                        'sort_order' => $itemConfig['sort_order'] ?? 0,
                    ]
                );

                $itemsByKey[$item->key] = $item;
            }

            foreach ($rolesConfig as $roleConfig) {
                $role = $rolesBySlug[$roleConfig['slug']];
                $keys = $roleConfig['sidebar_item_keys'] ?? [];

                if (in_array('*', $keys, true)) {
                    $role->sidebarItems()->sync(SidebarItem::query()->pluck('id')->all());
                    continue;
                }

                $itemIds = collect($keys)
                    ->map(fn (string $key) => $itemsByKey[$key]->id ?? null)
                    ->filter()
                    ->values()
                    ->all();

                $role->sidebarItems()->sync($itemIds);
            }

            $superadminEmail = $inventoryConfig['superadmin_email'] ?? null;
            $superadminUsername = $inventoryConfig['superadmin_username'] ?? null;
            $superadminRole = $rolesBySlug['superadmin'] ?? null;

            if ($superadminRole && ($superadminEmail || $superadminUsername)) {
                $query = User::query()->where(function ($subQuery) use ($superadminEmail, $superadminUsername) {
                    if ($superadminEmail) {
                        $subQuery->where('email', $superadminEmail);
                    }

                    if ($superadminUsername) {
                        $subQuery->orWhere(function ($nameQuery) use ($superadminUsername) {
                            if (Schema::hasColumn('users', 'username')) {
                                $nameQuery->orWhere('username', $superadminUsername);
                            }

                            $nameQuery->orWhere('name', $superadminUsername);
                        });
                    }
                });

                $query->get()->each(function (User $user) use ($superadminRole) {
                    $user->role_id = $superadminRole->id;
                    $user->save();
                });
            }
        });
    }
}
