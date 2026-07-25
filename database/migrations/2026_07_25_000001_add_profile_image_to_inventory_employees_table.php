<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory.employees')) {
            return;
        }

        if (!Schema::hasColumn('inventory.employees', 'profile_image')) {
            Schema::table('inventory.employees', function (Blueprint $table) {
                $table->string('profile_image')->nullable()->after('position');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory.employees') && Schema::hasColumn('inventory.employees', 'profile_image')) {
            Schema::table('inventory.employees', function (Blueprint $table) {
                $table->dropColumn('profile_image');
            });
        }
    }
};