<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees_leave_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('leave_type');
            $table->date('date_from');
            $table->date('date_to')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('pending_hr');
            $table->string('current_step')->default('hr');

            $table->unsignedBigInteger('hr_signed_by')->nullable();
            $table->timestamp('hr_signed_at')->nullable();
            $table->string('hr_signature_path')->nullable();

            $table->unsignedBigInteger('division_chief_signed_by')->nullable();
            $table->timestamp('division_chief_signed_at')->nullable();
            $table->string('division_chief_signature_path')->nullable();

            $table->unsignedBigInteger('regional_director_signed_by')->nullable();
            $table->timestamp('regional_director_signed_at')->nullable();
            $table->string('regional_director_signature_path')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('signing_notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['current_step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees_leave_history');
    }
};
