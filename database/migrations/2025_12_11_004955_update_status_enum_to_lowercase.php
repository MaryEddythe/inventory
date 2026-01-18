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
        // Update existing data
        DB::statement("UPDATE inventory_items SET status = CASE WHEN status = 'New' THEN '≤ 5 years' WHEN status = 'For Replacement' THEN '> 5 years' ELSE status END");

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->enum('status', ['≤ 5 years', '> 5 years'])->default('≤ 5 years')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->enum('status', ['NEW', 'FOR REPLACEMENT'])->default('NEW')->change();
        });
    }
};
