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
            if (!Schema::connection('icm')->hasColumn('icm', 'date_conducted')) {
                $table->date('date_conducted')->nullable()->after('icm_recommendations');
            }
            if (!Schema::connection('icm')->hasColumn('icm', 'time_started')) {
                $table->time('time_started')->nullable()->after('date_conducted');
            }
            if (!Schema::connection('icm')->hasColumn('icm', 'time_ended')) {
                $table->time('time_ended')->nullable()->after('time_started');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('icm')->table('icm', function (Blueprint $table) {
            $table->dropColumn(['date_conducted', 'time_started', 'time_ended']);
        });
    }
};
