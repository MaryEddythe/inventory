<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory.employees')) {
            return;
        }

        // Force-drop via raw SQL to avoid Laravel enum-alter edge cases.
        // This will remove the column regardless of its current enum definition.
        if (Schema::hasColumn('inventory.employees', 'employment_type')) {
            Schema::table('inventory.employees', function (Blueprint $table) {
                $table->dropColumn('employment_type');
            });
        }

        // Add enum with only unique values.
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

