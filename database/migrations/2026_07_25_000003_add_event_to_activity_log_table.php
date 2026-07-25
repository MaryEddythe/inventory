<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name', 'activity_log'), function (Blueprint $table) {
            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name', 'activity_log'), 'event')) {
                $table->string('event')->nullable()->after('batch_uuid');
                $table->index('event');
            }
        });
    }

    public function down(): void
    {
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name', 'activity_log'), function (Blueprint $table) {
            if (Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name', 'activity_log'), 'event')) {
                $table->dropIndex(['event']);
                $table->dropColumn('event');
            }
        });
    }
};
