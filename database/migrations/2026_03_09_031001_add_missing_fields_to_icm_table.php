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
        Schema::table('icm', function (Blueprint $table) {
            if (!Schema::hasColumn('icm', 'division')) {
                $table->string('division')->nullable()->after('icm_no');
            }
            if (!Schema::hasColumn('icm', 'classification')) {
                $table->string('classification')->nullable()->after('problem_description');
            }
            if (!Schema::hasColumn('icm', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('brand_model');
            }
            if (!Schema::hasColumn('icm', 'property_number')) {
                $table->string('property_number')->nullable()->after('serial_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('icm', function (Blueprint $table) {
            if (Schema::hasColumn('icm', 'division')) {
                $table->dropColumn('division');
            }
            if (Schema::hasColumn('icm', 'classification')) {
                $table->dropColumn('classification');
            }
            if (Schema::hasColumn('icm', 'serial_number')) {
                $table->dropColumn('serial_number');
            }
            if (Schema::hasColumn('icm', 'property_number')) {
                $table->dropColumn('property_number');
            }
        });
    }
};
