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
        DB::statement("
            UPDATE inventory_items
            SET `condition` = CASE
                WHEN TIMESTAMPDIFF(YEAR, date_acquired, CURDATE()) <= 5 THEN 'NEW'
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
