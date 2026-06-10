<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('other_ppes', function (Blueprint $table) {
            $table->enum('co_mooe', ['RPCSP', 'PPE'])
                ->nullable()
                ->after('unit_value');

            $table->index('co_mooe');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('other_ppes', function (Blueprint $table) {
            $table->dropIndex(['co_mooe']);
            $table->dropColumn('co_mooe');
        });
    }

};
