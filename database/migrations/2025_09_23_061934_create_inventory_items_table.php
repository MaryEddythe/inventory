<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id('no'); 
            $table->string('division', 100);
            $table->string('enduser', 100);
            $table->string('classification', 100);
            $table->text('description'); 
            $table->string('serial_number')->nullable()->unique();
            $table->string('property_number')->unique();
            $table->decimal('unit_price', 10, 2);
            $table->string('co_mooe', 50);
            $table->date('date_acquired');
            $table->text('remarks')->nullable();
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
};