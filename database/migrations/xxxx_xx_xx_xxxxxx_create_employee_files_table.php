<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emp_no')->index();
            $table->string('file_type');
            $table->string('file_name');
            $table->string('file_url');
            $table->string('file_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['emp_no', 'file_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_files');
    }
};
