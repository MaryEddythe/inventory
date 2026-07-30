<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees_leave_history', function (Blueprint $table) {
            if (! Schema::hasColumn('employees_leave_history', 'cto_leave_history_id')) {
                $table->unsignedBigInteger('cto_leave_history_id')->nullable()->after('reason');
            }
            if (! Schema::hasColumn('employees_leave_history', 'cto_remarks')) {
                $table->text('cto_remarks')->nullable()->after('cto_leave_history_id');
            }
            if (! Schema::hasColumn('employees_leave_history', 'cto_duration')) {
                $table->string('cto_duration', 20)->nullable()->after('cto_remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees_leave_history', function (Blueprint $table) {
            $columns = collect(['cto_duration', 'cto_remarks', 'cto_leave_history_id'])
                ->filter(fn ($column) => Schema::hasColumn('employees_leave_history', $column))
                ->all();
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
