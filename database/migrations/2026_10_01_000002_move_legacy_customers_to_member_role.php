<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The WordPress import mapped customer/guest/user accounts (and anyone
     * with no role meta) to 'host', which let customers into the host
     * dashboard. Move them to 'member'.
     *
     * Only accounts that are clearly not hosts are touched: imported from
     * WordPress (legacy IDs below 900000; admin-created users get 900000+),
     * owning no apartment, not linked to a management company, not on a host
     * team and with no host application.
     */
    public function up(): void
    {
        $query = DB::table('users')
            ->where('role', 'host')
            ->whereNotNull('legacy_wp_id')
            ->where('legacy_wp_id', '<', 900000)
            ->whereNull('management_company_id')
            ->whereNotExists(fn ($q) => $q->from('vv_apartments')->whereColumn('vv_apartments.user_id', 'users.legacy_wp_id'));

        foreach ([
            ['vv_host_management_companies', 'user_id', 'id'],
            ['vv_host_team_members', 'user_id', 'id'],
            ['vv_host_applications', 'user_id', 'legacy_wp_id'],
        ] as [$table, $column, $userColumn]) {
            if (Schema::hasTable($table)) {
                $query->whereNotExists(fn ($q) => $q->from($table)->whereColumn("{$table}.{$column}", "users.{$userColumn}"));
            }
        }

        $query->update(['role' => 'member']);
    }

    /**
     * Not reversible: after this runs, real members register as 'member'
     * too, and turning them into hosts would hand them the dashboard.
     */
    public function down(): void {}
};
