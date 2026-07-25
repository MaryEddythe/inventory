<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees_leave_history', function (Blueprint $table) {
            if (! Schema::hasColumn('employees_leave_history', 'applicant_signature_path')) {
                $table->string('applicant_signature_path')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('employees_leave_history', 'applicant_signed_at')) {
                $table->timestamp('applicant_signed_at')->nullable()->after('applicant_signature_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees_leave_history', function (Blueprint $table) {
            if (Schema::hasColumn('employees_leave_history', 'applicant_signed_at')) {
                $table->dropColumn('applicant_signed_at');
            }

            if (Schema::hasColumn('employees_leave_history', 'applicant_signature_path')) {
                $table->dropColumn('applicant_signature_path');
            }
        });
    }
};
