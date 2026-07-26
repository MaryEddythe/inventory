<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $roles = [
            ['slug' => 'division-chief-ord', 'name' => 'ORD Division Chief'],
            ['slug' => 'division-chief-msesdd', 'name' => 'MSESDD Division Chief'],
            ['slug' => 'division-chief-mmd', 'name' => 'MMD Division Chief'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'is_superadmin' => false,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $baseRoleId = DB::table('roles')->where('slug', 'division-chief')->value('id');
        $baseSidebarItemIds = $baseRoleId
            ? DB::table('role_sidebar_item')->where('role_id', $baseRoleId)->pluck('sidebar_item_id')->all()
            : [];

        if (empty($baseSidebarItemIds)) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('slug', array_column($roles, 'slug'))
            ->pluck('id', 'slug');

        $pivotRows = [];
        foreach ($roleIds as $roleId) {
            foreach ($baseSidebarItemIds as $sidebarItemId) {
                $pivotRows[] = [
                    'role_id' => $roleId,
                    'sidebar_item_id' => $sidebarItemId,
                ];
            }
        }

        DB::table('role_sidebar_item')->insertOrIgnore($pivotRows);
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $roleSlugs = [
            'division-chief-ord',
            'division-chief-msesdd',
            'division-chief-mmd',
        ];

        $roleIds = DB::table('roles')
            ->whereIn('slug', $roleSlugs)
            ->pluck('id')
            ->all();

        if (! empty($roleIds)) {
            DB::table('role_sidebar_item')
                ->whereIn('role_id', $roleIds)
                ->delete();
        }

        DB::table('roles')
            ->whereIn('slug', $roleSlugs)
            ->delete();
    }
};
