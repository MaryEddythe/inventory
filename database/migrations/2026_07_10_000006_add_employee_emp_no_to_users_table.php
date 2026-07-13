<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Your Employee model uses `emp_no` as primary key.
            // We'll store that key directly in users.
            if (!Schema::hasColumn('users', 'employee')) {
                $table->string('employee')->nullable()->after('emp_no');
            }

            if (!Schema::hasColumn('users', 'emp_no')) {
                // In case earlier migrations used a different column name.
                $table->string('emp_no')->nullable()->after('name');
            }
        });

        // NOTE: Add foreign key only if your MySQL setup allows cross-schema FK
        // (employees table is under `inventory.employees`).
        // This migration intentionally skips FK to avoid deployment issues.
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'employee')) {
                $table->dropColumn('employee');
            }

            // Do NOT drop users.emp_no automatically; it may already exist.
        });
    }
};

