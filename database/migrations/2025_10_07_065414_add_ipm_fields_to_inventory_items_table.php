<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new columns if they don't exist
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'system_boot_up')) {
                $table->boolean('system_boot_up')->default(false)->after('status');
            }
            if (!Schema::hasColumn('inventory_items', 'hardware')) {
                $table->boolean('hardware')->default(false)->after('system_boot_up');
            }
            if (!Schema::hasColumn('inventory_items', 'performance')) {
                $table->boolean('performance')->default(false)->after('hardware');
            }
            if (!Schema::hasColumn('inventory_items', 'cables_connections')) {
                $table->boolean('cables_connections')->default(false)->after('performance');
            }
            if (!Schema::hasColumn('inventory_items', 'peripherals')) {
                $table->boolean('peripherals')->default(false)->after('cables_connections');
            }
            if (!Schema::hasColumn('inventory_items', 'recommendation')) {
                $table->text('recommendation')->nullable()->after('peripherals');
            }
            if (!Schema::hasColumn('inventory_items', 'date_conducted')) {
                $table->date('date_conducted')->nullable()->after('recommendation');
            }
            if (!Schema::hasColumn('inventory_items', 'time_started')) {
                $table->time('time_started')->nullable()->after('date_conducted');
            }
            if (!Schema::hasColumn('inventory_items', 'time_ended')) {
                $table->time('time_ended')->nullable()->after('time_started');
            }
        });

        // Change status enum using raw SQL
        DB::statement("ALTER TABLE inventory_items MODIFY COLUMN status ENUM('NEW', 'FOR REPLACEMENT', 'Functional', 'Nonfunctional') DEFAULT 'Functional'");
        DB::statement("UPDATE inventory_items SET status = 'Functional'");
        DB::statement("ALTER TABLE inventory_items MODIFY COLUMN status ENUM('Functional', 'Nonfunctional') DEFAULT 'Functional'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['system_boot_up', 'hardware', 'performance', 'cables_connections', 'peripherals', 'recommendation', 'date_conducted', 'time_started', 'time_ended']);
            $table->enum('status', ['NEW', 'FOR REPLACEMENT'])->default('NEW')->change();
        });
    }
};
