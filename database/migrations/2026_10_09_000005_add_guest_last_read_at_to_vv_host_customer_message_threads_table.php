<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets the customer dashboard show "1 unread message from your host".
     */
    public function up(): void
    {
        Schema::table('vv_host_customer_message_threads', function (Blueprint $table) {
            $table->timestamp('guest_last_read_at', 6)->nullable()->after('host_last_read_at');
        });
    }

    public function down(): void
    {
        Schema::table('vv_host_customer_message_threads', function (Blueprint $table) {
            $table->dropColumn('guest_last_read_at');
        });
    }
};
