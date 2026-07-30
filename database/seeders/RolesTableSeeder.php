<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id' => 1,
                'name' => 'Super Admin',
                'slug' => 'superadmin',
                'is_superadmin' => 1,
                'created_at' => '2026-07-17 06:28:00',
                'updated_at' => '2026-07-17 06:28:00',
            ],
            [
                'id' => 2,
                'name' => 'Employee',
                'slug' => 'employee',
                'is_superadmin' => 0,
                'created_at' => '2026-07-17 06:28:00',
                'updated_at' => '2026-07-17 06:28:00',
            ],
            [
                'id' => 3,
                'name' => 'Division Chief',
                'slug' => 'division-chief-fad',
                'is_superadmin' => 0,
                'created_at' => '2026-07-24 01:33:09',
                'updated_at' => '2026-07-24 01:33:09',
            ],
            [
                'id' => 4,
                'name' => 'HR',
                'slug' => 'hr',
                'is_superadmin' => 0,
                'created_at' => '2026-07-24 03:32:35',
                'updated_at' => '2026-07-24 03:32:35',
            ],
            [
                'id' => 5,
                'name' => 'Regional Director',
                'slug' => 'rd',
                'is_superadmin' => 0,
                'created_at' => '2026-07-24 05:45:18',
                'updated_at' => '2026-07-24 05:45:18',
            ],
            [
                'id' => 6,
                'name' => 'ORD Division Chief',
                'slug' => 'division-chief-ord',
                'is_superadmin' => 0,
                'created_at' => '2026-07-26 11:01:16',
                'updated_at' => '2026-07-26 11:01:16',
            ],
            [
                'id' => 7,
                'name' => 'MSESDD Division Chief',
                'slug' => 'division-chief-msesdd',
                'is_superadmin' => 0,
                'created_at' => '2026-07-26 11:01:16',
                'updated_at' => '2026-07-26 11:01:16',
            ],
            [
                'id' => 8,
                'name' => 'MMD Division Chief',
                'slug' => 'division-chief-mmd',
                'is_superadmin' => 0,
                'created_at' => '2026-07-26 11:01:16',
                'updated_at' => '2026-07-26 11:01:16',
            ],
            [
                'id' => 9,
                'name' => 'GD Division Chief',
                'slug' => 'division-chief-gd',
                'is_superadmin' => 0,
                'created_at' => '2026-07-28 13:35:49',
                'updated_at' => '2026-07-28 13:35:49',
            ],
            [
                'id' => 10,
                'name' => 'FAD Division Chief',
                'slug' => 'division-chief',
                'is_superadmin' => 0,
                'created_at' => '2026-07-28 09:36:48',
                'updated_at' => '2026-07-28 09:36:48',
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}