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
        Schema::table('inventory_items', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_items', 'serial_number') && Schema::getColumnListing('inventory_items_serial_number_unique')) {
                $table->dropIndex('inventory_items_serial_number_unique');
            }
            if (Schema::hasColumn('inventory_items', 'property_number') && Schema::getColumnListing('inventory_items_property_number_unique')) {
                $table->dropIndex('inventory_items_property_number_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->unique('serial_number');
            $table->unique('property_number');
        });
    }
};
