<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links a booking to the customer account that placed it. Legacy
     * vv_bookings.user_id holds WordPress IDs, so it can't be reused for
     * Laravel user IDs.
     */
    public function up(): void
    {
        Schema::table('vv_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('member_user_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('vv_bookings', function (Blueprint $table) {
            $table->dropIndex(['member_user_id']);
            $table->dropColumn('member_user_id');
        });
    }
};
