<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // BLOCKED: This migration previously caused ENUM duplication errors.
        // Keep it as no-op so other later migrations can run.
        return;
    }

    public function down(): void
    {
        if (Schema::hasColumn('inventory.employees', 'employment_type')) {
            Schema::table('inventory.employees', function (Blueprint $table) {
                $table->dropColumn('employment_type');
            });
        }
    }
};

