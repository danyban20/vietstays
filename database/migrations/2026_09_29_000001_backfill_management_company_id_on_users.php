<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Companies created before users.management_company_id existed never
     * linked their owner, so the admin page showed them with 0 hosts and
     * 0 apartments. Link each owner to the company they created, leaving
     * anyone already linked alone.
     */
    public function up(): void
    {
        $companies = DB::table('vv_host_management_companies')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->get(['id', 'user_id']);

        foreach ($companies as $company) {
            DB::table('users')
                ->where('id', $company->user_id)
                ->whereNull('management_company_id')
                ->update(['management_company_id' => $company->id]);
        }
    }

    /**
     * Nothing to undo: backfilled links are indistinguishable from ones set
     * by the wizard, and the column itself is dropped by the migration that
     * added it.
     */
    public function down(): void {}
};
