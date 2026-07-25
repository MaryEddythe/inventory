<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name', 'activity_log'), function (Blueprint $table) {
            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name', 'activity_log'), 'batch_uuid')) {
                $table->uuid('batch_uuid')->nullable()->after('causer_type');
                $table->index('batch_uuid');
            }
        });
    }

    public function down(): void
    {
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name', 'activity_log'), function (Blueprint $table) {
            if (Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name', 'activity_log'), 'batch_uuid')) {
                $table->dropIndex(['batch_uuid']);
                $table->dropColumn('batch_uuid');
            }
        });
    }
};
