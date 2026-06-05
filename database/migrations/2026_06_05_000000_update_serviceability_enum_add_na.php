<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update the ENUM definition to include 'N/A'
        // MySQL-specific ALTER TABLE ... MODIFY syntax.
        DB::statement("
            ALTER TABLE inventory_items
            MODIFY serviceability ENUM('Beyond Economic Repair','Good Condition','For Replacement','N/A') NULL
        ");
    }

    public function down(): void
    {
        // Revert ENUM definition (remove 'N/A')
        DB::statement("
            ALTER TABLE inventory_items
            MODIFY serviceability ENUM('Beyond Economic Repair','Good Condition','For Replacement') NULL
        ");
    }
};
