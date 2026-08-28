<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->date('attendance_date');
            $table->string('status')->default('present');
            $table->time('check_in_at')->nullable();
            $table->unsignedInteger('minutes_late')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('late_warning_sent_at')->nullable();
            $table->timestamp('memo_flagged_at')->nullable();
            $table->timestamp('absence_follow_up_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
        });

        if (! Schema::hasTable('sidebar_items')) {
            return;
        }

        DB::table('sidebar_items')->updateOrInsert(
            ['key' => 'attendance'],
            [
                'label' => 'Attendance',
                'route_name' => 'attendance.index',
                'route_pattern' => 'attendance.*',
                'icon' => 'bi bi-clock-history',
                'parent_id' => null,
                'sort_order' => 65,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $attendanceId = DB::table('sidebar_items')->where('key', 'attendance')->value('id');
        $hrRoleId = DB::table('roles')->where('slug', 'hr')->value('id');

        if ($attendanceId && $hrRoleId) {
            DB::table('role_sidebar_item')->insertOrIgnore([
                'role_id' => $hrRoleId,
                'sidebar_item_id' => $attendanceId,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('role_sidebar_item')) {
            $attendanceId = DB::table('sidebar_items')->where('key', 'attendance')->value('id');

            if ($attendanceId) {
                DB::table('role_sidebar_item')->where('sidebar_item_id', $attendanceId)->delete();
            }
        }

        if (Schema::hasTable('sidebar_items')) {
            DB::table('sidebar_items')->where('key', 'attendance')->delete();
        }

        Schema::dropIfExists('attendance_records');
    }
};
