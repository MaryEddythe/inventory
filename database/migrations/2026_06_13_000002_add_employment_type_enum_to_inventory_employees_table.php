<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add enum to the inventory module's employees table
        // (table name expected: inventory.employees)
        Schema::table('employees', function (Blueprint $table) {
            // If this column already exists, avoid duplicate failures
            if (!Schema::hasColumn('employees', 'employment_type')) {
                $table->enum('employment_type', ['COS', 'Permanent'])->nullable()->after('position');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'employment_type')) {
                $table->dropColumn('employment_type');
            }
        });
    }
};
