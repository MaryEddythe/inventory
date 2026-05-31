<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE cips MODIFY date_acquired DATE NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE cips MODIFY date_acquired DATE NOT NULL');
    }
};
