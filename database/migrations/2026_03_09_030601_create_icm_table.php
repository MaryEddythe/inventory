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
        if (!Schema::hasTable('icm')) {
            Schema::create('icm', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('inventory_item_id');
                $table->string('icm_no')->nullable()->unique();
                $table->text('problem_description')->nullable();
                $table->enum('icm_type', ['Assistance', 'Troubleshoot'])->nullable();
                $table->enum('priority', ['P1-Critical', 'P2-Important', 'P3-Normal', 'P4-Low'])->nullable();
                $table->string('requesting_personnel')->nullable();
                $table->date('open_date')->nullable();
                $table->time('open_time')->nullable();
                $table->date('close_date')->nullable();
                $table->time('close_time')->nullable();
                $table->enum('hardware_software', ['Hardware', 'Software'])->nullable();
                $table->string('brand_model')->nullable();
                $table->text('icm_findings')->nullable();
                $table->text('actions_taken')->nullable();
                $table->text('icm_recommendations')->nullable();
                $table->timestamps();

                $table->foreign('inventory_item_id')->references('id')->on('inventory_items');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('icm');
    }
};
