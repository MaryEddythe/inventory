<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This app expects columns to live on inventory.employees.
        if (!Schema::hasTable('inventory.employees')) {
            return;
        }

        if (!Schema::hasColumn('inventory.employees', 'drive')) {
            // Store all Google Drive links generated for the employee on creation.
            // Use JSON text so we can store multiple links later.
            // Avoid `after(...)` because some environments may not have `drive_folder_url` yet.
            Schema::table('inventory.employees', function (Blueprint $table) {
                $table->longText('drive')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory.employees') && Schema::hasColumn('inventory.employees', 'drive')) {
            Schema::table('inventory.employees', function (Blueprint $table) {
                $table->dropColumn('drive');
            });
        }
    }
};

