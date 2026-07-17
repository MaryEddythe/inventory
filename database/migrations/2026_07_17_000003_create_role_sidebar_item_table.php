<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_sidebar_item', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sidebar_item_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'sidebar_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_sidebar_item');
    }
};
