<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sidebar_items')) {
            return;
        }

        $itemId = DB::table('sidebar_items')->where('key', 'attendance-holidays')->value('id');

        if (! $itemId) {
            return;
        }

        if (Schema::hasTable('role_sidebar_item')) {
            DB::table('role_sidebar_item')->where('sidebar_item_id', $itemId)->delete();
        }

        DB::table('sidebar_items')->where('id', $itemId)->delete();
    }

    public function down(): void
    {
        //
    }
};
