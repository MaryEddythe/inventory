<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nest the existing "Leave Applications" and "Leave Ledgers" sidebar items
 * under the "Leave Credits" dropdown instead of rendering them as top-level
 * items. Mirrors the parent_key change made in config/inventory.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sidebar_items')) {
            return;
        }

        $leaveCreditsId = DB::table('sidebar_items')->where('key', 'leave-credits')->value('id');

        if (! $leaveCreditsId) {
            return;
        }

        DB::table('sidebar_items')->where('key', 'leave-applications')->update([
            'parent_id' => $leaveCreditsId,
            'sort_order' => 20,
        ]);

        DB::table('sidebar_items')->where('key', 'leave-ledgers')->update([
            'parent_id' => $leaveCreditsId,
            'sort_order' => 30,
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('sidebar_items')) {
            return;
        }

        DB::table('sidebar_items')->where('key', 'leave-applications')->update([
            'parent_id' => null,
            'sort_order' => 65,
        ]);

        DB::table('sidebar_items')->where('key', 'leave-ledgers')->update([
            'parent_id' => null,
            'sort_order' => 66,
        ]);
    }
};