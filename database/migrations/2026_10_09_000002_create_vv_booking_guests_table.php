<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Passport details per guest on a booking, which the host needs to
     * register foreign guests' temporary stay. Passport number and birth
     * date are stored encrypted (see BookingGuest casts).
     */
    public function up(): void
    {
        Schema::create('vv_booking_guests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('booking_id');
            $table->unsignedTinyInteger('position');
            $table->string('full_name', 150);
            $table->string('nationality', 2);
            $table->text('passport_number');
            $table->text('date_of_birth');
            $table->date('passport_expiry');
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->unique(['booking_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_booking_guests');
    }
};
