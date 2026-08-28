<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_records', 'schedule_type')) {
                $table->string('schedule_type')->default('regular')->after('employee_id');
            }

            if (! Schema::hasColumn('attendance_records', 'check_out_at')) {
                $table->time('check_out_at')->nullable()->after('check_in_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_records', 'schedule_type')) {
                $table->dropColumn('schedule_type');
            }

            if (Schema::hasColumn('attendance_records', 'check_out_at')) {
                $table->dropColumn('check_out_at');
            }
        });
    }
};
