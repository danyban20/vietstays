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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique()->after('id');
            $table->string('role')->default('host')->after('password');
            $table->string('display_name')->nullable()->after('role');
            $table->string('phone')->nullable()->after('display_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['legacy_wp_id', 'role', 'display_name', 'phone']);
        });
    }
};
