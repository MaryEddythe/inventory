<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The live `employee_files.file_type` column was altered out-of-band into an
 * ENUM that only allowed ('PDS','SALN','NBI CLEARANCE','MEDICAL CERTIFICATE').
 * That caused MySQL warning 1265 (Data truncated) whenever the app tried to
 * store other valid file types such as 'Civil Service Eligibility'.
 *
 * This reverts the column back to a plain VARCHAR, which matches the original
 * migration and the full set of file types the app supports.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_files', function (Blueprint $table) {
            $table->string('file_type', 255)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('employee_files', function (Blueprint $table) {
            $table->enum('file_type', [
                'PDS',
                'SALN',
                'NBI CLEARANCE',
                'MEDICAL CERTIFICATE',
            ])->nullable(false)->change();
        });
    }
};