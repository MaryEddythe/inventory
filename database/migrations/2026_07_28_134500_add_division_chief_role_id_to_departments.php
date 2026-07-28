<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('inventory')->hasTable('departments')) {
            return;
        }

        if (! Schema::connection('inventory')->hasColumn('departments', 'division_chief_role_id')) {
            Schema::connection('inventory')->table('departments', function (Blueprint $table) {
                $table->unsignedBigInteger('division_chief_role_id')->nullable()->after('description');
            });
        }

        $departmentChiefRoles = [
            1 => 3,
            3 => 6,
            5 => 9,
            6 => 8,
            4 => 7,
        ];

        foreach ($departmentChiefRoles as $deptNo => $roleId) {
            DB::connection('inventory')
                ->table('departments')
                ->where('dept_no', $deptNo)
                ->update(['division_chief_role_id' => $roleId]);
        }
    }

    public function down(): void
    {
        if (
            Schema::connection('inventory')->hasTable('departments')
            && Schema::connection('inventory')->hasColumn('departments', 'division_chief_role_id')
        ) {
            Schema::connection('inventory')->table('departments', function (Blueprint $table) {
                $table->dropColumn('division_chief_role_id');
            });
        }
    }
};
