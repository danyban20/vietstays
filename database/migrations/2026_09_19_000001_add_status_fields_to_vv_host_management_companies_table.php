<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vv_host_management_companies', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('user_id');
            $table->string('company_number')->nullable()->after('name');
            $table->string('rejection_reason')->nullable()->after('status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('rejection_reason');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        // Allow "+ Add company" shell records with no host linked yet.
        DB::statement('ALTER TABLE vv_host_management_companies MODIFY user_id BIGINT UNSIGNED NULL');

        // Rows that already existed were auto-activated under the old flow.
        DB::table('vv_host_management_companies')->update(['status' => 'active']);
    }

    public function down(): void
    {
        Schema::table('vv_host_management_companies', function (Blueprint $table) {
            $table->dropColumn(['status', 'company_number', 'rejection_reason', 'reviewed_by', 'reviewed_at']);
        });

        DB::statement('ALTER TABLE vv_host_management_companies MODIFY user_id BIGINT UNSIGNED NOT NULL');
    }
};
