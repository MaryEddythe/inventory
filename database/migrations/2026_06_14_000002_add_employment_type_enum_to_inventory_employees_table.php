<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure correct enum column exists on inventory.employees.
        if (!Schema::hasTable('inventory.employees')) {
            return;
        }

        if (Schema::hasColumn('inventory.employees', 'employment_type')) {
            // Drop and recreate to ensure correct enum values.
            Schema::table('inventory.employees', function (Blueprint $table) {
                $table->dropColumn('employment_type');
            });
        }

        Schema::table('inventory.employees', function (Blueprint $table) {
            $table->enum('employment_type', ['COS', 'Permanent'])->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory.employees') && Schema::hasColumn('inventory.employees', 'employment_type')) {
            Schema::table('inventory.employees', function (Blueprint $table) {
                $table->dropColumn('employment_type');
            });
        }
    }
};

