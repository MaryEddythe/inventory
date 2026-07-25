<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if the column already exists
        $schema = Schema::connection('mysql');
        
        if (!$schema->hasColumn('inventory.employees', 'profile_image')) {
            $schema->table('inventory.employees', function (Blueprint $table) {
                $table->string('profile_image')->nullable();
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql');
        
        if ($schema->hasColumn('inventory.employees', 'profile_image')) {
            $schema->table('inventory.employees', function (Blueprint $table) {
                $table->dropColumn('profile_image');
            });
        }
    }
};
