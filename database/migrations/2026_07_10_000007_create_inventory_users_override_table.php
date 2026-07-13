<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create an override table under the `inventory` schema.
        // This table is used to store account credentials/policy per emp_no
        // without hardcoding anything in PHP.

        if (!Schema::connection('mysql')->hasTable('users')) {
            // no-op: this migration assumes your environment already has `inventory.users`
            // If your DB doesn’t support schemas, create this in your main DB instead.
        }

        // NOTE: Laravel Schema builder doesn’t natively support schema namespaces.
        // If you already created `inventory.users`, you can skip this migration.
        // Keeping migration minimal: it only creates `inventory_users_override` as a fallback.

        if (!Schema::hasTable('inventory_users_override')) {
            Schema::create('inventory_users_override', function (Blueprint $table) {
                $table->id();
                $table->string('emp_no')->unique();
                $table->string('email')->nullable();
                $table->string('password')->nullable(); // store bcrypt hash
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_users_override');
    }
};

