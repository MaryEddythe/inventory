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
        Schema::connection('icm')->table('icm', function (Blueprint $table) {
            if (!Schema::connection('icm')->hasColumn('icm', 'division')) {
                $table->string('division')->nullable()->after('icm_no');
            }
            if (!Schema::connection('icm')->hasColumn('icm', 'classification')) {
                $table->string('classification')->nullable()->after('problem_description');
            }
            if (!Schema::connection('icm')->hasColumn('icm', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('brand_model');
            }
            if (!Schema::connection('icm')->hasColumn('icm', 'property_number')) {
                $table->string('property_number')->nullable()->after('serial_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('icm')->table('icm', function (Blueprint $table) {
            $table->dropColumn([
                'division',
                'classification',
                'serial_number',
                'property_number',
            ]);
        });
    }
};
