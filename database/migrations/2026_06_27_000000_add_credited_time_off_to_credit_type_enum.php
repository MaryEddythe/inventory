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
        Schema::table('employee_leave_benefits', function (Blueprint $table) {
            $table->enum('credit_type', [
                'Vacation Leave',
                'Sick Leave',
                'Wellness Leave',
                'Special Privilege Leave',
                'Maternity Leave',
                'Paternity Leave',
                'Solo Parent Leave',
                'Rehabilitation Leave',
                'Special Emergency Leave',
                'Credited Time-Off'
            ])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_benefits', function (Blueprint $table) {
            $table->enum('credit_type', [
                'Vacation Leave',
                'Sick Leave',
                'Wellness Leave',
                'Special Privilege Leave',
                'Maternity Leave',
                'Paternity Leave',
                'Solo Parent Leave',
                'Rehabilitation Leave',
                'Special Emergency Leave'
            ])->nullable()->change();
        });
    }
};
