<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite compatibility, use strftime instead of TIMESTAMPDIFF
        DB::statement("
            UPDATE inventory_items
            SET `condition` = CASE
                WHEN (strftime('%Y', 'now') - strftime('%Y', date_acquired)) <= 5 THEN 'NEW'
                ELSE 'FOR REPLACEMENT'
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as status will be recalculated on next update
    }
};
