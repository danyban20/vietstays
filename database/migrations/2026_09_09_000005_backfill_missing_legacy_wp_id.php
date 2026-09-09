<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Every apartment/booking/customer ownership check in this app keys off
     * User::legacy_wp_id (matching apartments.user_id, the legacy WordPress
     * post-author id). Users created via the admin "Add user" form never had
     * one set, which made every ownership-scoped write for that account fail
     * with a NOT NULL constraint violation. Backfill a synthetic id, using
     * the same high-range convention as DistrictCodeSeeder to guarantee no
     * collision with real WordPress-imported ids (max seen: in the low
     * hundreds).
     */
    public function up(): void
    {
        DB::statement('UPDATE users SET legacy_wp_id = 900000 + id WHERE legacy_wp_id IS NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE users SET legacy_wp_id = NULL WHERE legacy_wp_id >= 900001');
    }
};
