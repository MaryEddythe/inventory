<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leave_ledger_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emp_no')->unique();
            $table->date('first_day_of_service')->nullable();
            $table->date('opening_balance_date')->nullable();
            $table->decimal('opening_vacation_balance', 8, 3)->default(0);
            $table->decimal('opening_sick_balance', 8, 3)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_ledger_settings');
    }
};
