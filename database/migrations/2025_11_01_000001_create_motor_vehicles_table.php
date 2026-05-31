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
        Schema::create('motor_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('article');
            $table->text('description');
            $table->string('property_number')->unique();
            $table->decimal('unit_value', 12, 2);
            $table->date('date_acquired');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index('property_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motor_vehicles');
    }
};
