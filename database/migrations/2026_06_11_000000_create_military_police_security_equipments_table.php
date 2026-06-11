<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('military_police_security_equipments', function (Blueprint $table) {
            $table->id();

            $table->string('article');
            $table->text('description');
            $table->string('property_number');
            $table->decimal('unit_value', 12, 2);

            $table->enum('co_mooe', ['RPCSP', 'PPE'])->nullable();

            $table->date('date_acquired');
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('property_number');
            $table->index('co_mooe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('military_police_security_equipments');
    }
};
