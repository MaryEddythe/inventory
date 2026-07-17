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
        DB::transaction(function () {
            $superadmin = Role::updateOrCreate(
                ['slug' => 'superadmin'],
                ['name' => 'Super Admin', 'is_superadmin' => true]
            );

            $employeeRole = Role::updateOrCreate(
                ['slug' => 'employee'],
                ['name' => 'Employee', 'is_superadmin' => false]
            );

            SidebarItem::updateOrCreate(
                ['key' => 'dashboard'],
                ['label' => 'Dashboard', 'route_name' => 'inventory.dashboard', 'route_pattern' => 'inventory.dashboard', 'icon' => 'bi bi-speedometer2', 'parent_id' => null, 'sort_order' => 10]
            );

            $inventory = SidebarItem::updateOrCreate(
                ['key' => 'inventory'],
                ['label' => 'Inventory', 'route_name' => null, 'route_pattern' => 'inventory.*', 'icon' => 'bi bi-archive', 'parent_id' => null, 'sort_order' => 20]
            );

            SidebarItem::updateOrCreate(
                ['key' => 'inventory.index'],
                ['label' => 'Inventory', 'route_name' => 'inventory.index', 'route_pattern' => 'inventory.index', 'icon' => null, 'parent_id' => $inventory->id, 'sort_order' => 10]
            );

            $inventoryTabs = [
                ['key' => 'inventory.tabs.moto-vehicle', 'label' => 'Motor Vehicle', 'route_name' => 'inventory.tabs.moto-vehicle', 'sort_order' => 20],
                ['key' => 'inventory.tabs.cip', 'label' => 'CIP', 'route_name' => 'inventory.tabs.cip', 'sort_order' => 30],
                ['key' => 'inventory.tabs.machine-equipment', 'label' => 'Machine & Equipment', 'route_name' => 'inventory.tabs.machine-equipment', 'sort_order' => 40],
                ['key' => 'inventory.tabs.office-equipment', 'label' => 'Office Equipment', 'route_name' => 'inventory.tabs.office-equipment', 'sort_order' => 50],
                ['key' => 'inventory.tabs.technical-scientific-equipment', 'label' => 'Technical and Scientific Equipment', 'route_name' => 'inventory.tabs.technical-scientific-equipment', 'sort_order' => 60],
                ['key' => 'inventory.tabs.other-ppe', 'label' => 'Other PPE', 'route_name' => 'inventory.tabs.other-ppe', 'sort_order' => 70],
                ['key' => 'inventory.tabs.furniture-fixtures', 'label' => 'Furnitures and Fixtures', 'route_name' => 'inventory.tabs.furniture-fixtures', 'sort_order' => 80],
                ['key' => 'inventory.tabs.military-police-security', 'label' => 'Military, Police & Security Equipment', 'route_name' => 'inventory.tabs.military-police-security', 'sort_order' => 90],
            ];

            foreach ($inventoryTabs as $item) {
                SidebarItem::updateOrCreate(
                    ['key' => $item['key']],
                    [
                        'label' => $item['label'],
                        'route_name' => $item['route_name'],
                        'route_pattern' => $item['route_name'],
                        'icon' => null,
                        'parent_id' => $inventory->id,
                        'sort_order' => $item['sort_order'],
                    ]
                );
            }

            SidebarItem::updateOrCreate(
                ['key' => 'inventory.ipm'],
                ['label' => 'IPM', 'route_name' => 'inventory.ipm', 'route_pattern' => 'inventory.ipm', 'icon' => 'bi bi-clipboard-check', 'parent_id' => null, 'sort_order' => 30]
            );

            SidebarItem::updateOrCreate(
                ['key' => 'inventory.icm'],
                ['label' => 'ICM', 'route_name' => 'inventory.icm', 'route_pattern' => 'inventory.icm', 'icon' => 'bi bi-tools', 'parent_id' => null, 'sort_order' => 40]
            );

            $employees = SidebarItem::updateOrCreate(
                ['key' => 'employees'],
                ['label' => 'Employees', 'route_name' => 'employees.index', 'route_pattern' => 'employees.*', 'icon' => 'bi bi-people', 'parent_id' => null, 'sort_order' => 50]
            );

            $calendar = SidebarItem::updateOrCreate(
                ['key' => 'calendar'],
                ['label' => 'Calendar', 'route_name' => 'calendar.index', 'route_pattern' => 'calendar.*', 'icon' => 'bi bi-calendar3', 'parent_id' => null, 'sort_order' => 60]
            );

            $leaveCredits = SidebarItem::updateOrCreate(
                ['key' => 'leave-credits'],
                ['label' => 'Leave Credits', 'route_name' => null, 'route_pattern' => 'credits.*', 'icon' => 'bi bi-wallet2', 'parent_id' => null, 'sort_order' => 70]
            );

            SidebarItem::updateOrCreate(
                ['key' => 'credits.cto'],
                ['label' => 'CTO', 'route_name' => 'credits.cto', 'route_pattern' => 'credits.cto', 'icon' => null, 'parent_id' => $leaveCredits->id, 'sort_order' => 10]
            );

            SidebarItem::updateOrCreate(
                ['key' => 'credits.index'],
                ['label' => 'Leave Credits', 'route_name' => 'credits.index', 'route_pattern' => 'credits.index', 'icon' => null, 'parent_id' => $leaveCredits->id, 'sort_order' => 20]
            );

            $superadmin->sidebarItems()->sync(SidebarItem::query()->pluck('id')->all());
            $employeeRole->sidebarItems()->sync([$employees->id, $calendar->id]);

            $ictUsers = User::query()
                ->where(function ($query) {
                    $query->where('name', 'like', '%ict%')
                        ->orWhere('email', 'like', '%ict%');

                    if (Schema::hasColumn('users', 'username')) {
                        $query->orWhere('username', 'like', '%ict%');
                    }
                })
                ->get();

            foreach ($ictUsers as $user) {
                $user->role_id = $superadmin->id;
                $user->save();
            }

            $glennUsers = User::query()
                ->where(function ($query) {
                    $query->where('name', 'like', '%glenn%')
                        ->orWhere('name', 'like', '%umipig%')
                        ->orWhere('email', 'like', '%glenn%');

                    if (Schema::hasColumn('users', 'username')) {
                        $query->orWhere('username', 'like', '%glenn%')
                            ->orWhere('username', 'like', '%umipig%');
                    }
                })
                ->get();

            foreach ($glennUsers as $user) {
                $user->role_id = $employeeRole->id;
                $user->save();
            }
        });
    }
}
