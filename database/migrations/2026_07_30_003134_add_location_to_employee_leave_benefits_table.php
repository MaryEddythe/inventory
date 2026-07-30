<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_leave_benefits', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_leave_benefits', 'location')) {
                $table->string('location')->nullable()->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_leave_benefits', function (Blueprint $table) {
            if (Schema::hasColumn('employee_leave_benefits', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};