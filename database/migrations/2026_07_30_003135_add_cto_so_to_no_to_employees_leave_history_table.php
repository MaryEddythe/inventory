<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees_leave_history', function (Blueprint $table) {
            if (! Schema::hasColumn('employees_leave_history', 'cto_so_to_no')) {
                $table->string('cto_so_to_no')->nullable()->after('cto_remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees_leave_history', function (Blueprint $table) {
            if (Schema::hasColumn('employees_leave_history', 'cto_so_to_no')) {
                $table->dropColumn('cto_so_to_no');
            }
        });
    }
};