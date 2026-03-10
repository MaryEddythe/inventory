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
            // ICM-specific fields
            if (!Schema::hasColumn('inventory_items', 'icm_no')) {
                $table->string('icm_no')->nullable()->unique()->after('property_number');
            }
            if (!Schema::hasColumn('inventory_items', 'problem_description')) {
                $table->text('problem_description')->nullable()->after('icm_no');
            }
            if (!Schema::hasColumn('inventory_items', 'icm_type')) {
                $table->enum('icm_type', ['Assistance', 'Troubleshoot'])->nullable()->after('problem_description');
            }
            if (!Schema::hasColumn('inventory_items', 'priority')) {
                $table->enum('priority', ['P1-Critical', 'P2-Important', 'P3-Normal', 'P4-Low'])->nullable()->after('icm_type');
            }
            if (!Schema::hasColumn('inventory_items', 'requesting_personnel')) {
                $table->string('requesting_personnel')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('inventory_items', 'open_date')) {
                $table->date('open_date')->nullable()->after('requesting_personnel');
            }
            if (!Schema::hasColumn('inventory_items', 'open_time')) {
                $table->time('open_time')->nullable()->after('open_date');
            }
            if (!Schema::hasColumn('inventory_items', 'close_date')) {
                $table->date('close_date')->nullable()->after('open_time');
            }
            if (!Schema::hasColumn('inventory_items', 'close_time')) {
                $table->time('close_time')->nullable()->after('close_date');
            }
            if (!Schema::hasColumn('inventory_items', 'hardware_software')) {
                $table->enum('hardware_software', ['Hardware', 'Software'])->nullable()->after('close_time');
            }
            if (!Schema::hasColumn('inventory_items', 'brand_model')) {
                $table->string('brand_model')->nullable()->after('hardware_software');
            }
            if (!Schema::hasColumn('inventory_items', 'icm_findings')) {
                $table->text('icm_findings')->nullable()->after('brand_model');
            }
            if (!Schema::hasColumn('inventory_items', 'actions_taken')) {
                $table->text('actions_taken')->nullable()->after('icm_findings');
            }
            if (!Schema::hasColumn('inventory_items', 'icm_recommendations')) {
                $table->text('icm_recommendations')->nullable()->after('actions_taken');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn([
                'icm_no',
                'problem_description',
                'icm_type',
                'priority',
                'requesting_personnel',
                'open_date',
                'open_time',
                'close_date',
                'close_time',
                'hardware_software',
                'brand_model',
                'icm_findings',
                'actions_taken',
                'icm_recommendations',
            ]);
        });
    }
};
