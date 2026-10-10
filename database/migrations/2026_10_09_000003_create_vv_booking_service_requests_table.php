<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extras a guest orders during a booking (for now: extra cleaning).
     * The host confirms or declines each one; it is paid on site.
     */
    public function up(): void
    {
        Schema::create('vv_booking_service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('booking_id')->index();
            $table->string('type', 30)->default('extra_cleaning');
            $table->date('service_date');
            $table->string('time_slot', 20);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('status', 20)->default('requested');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_booking_service_requests');
    }
};
