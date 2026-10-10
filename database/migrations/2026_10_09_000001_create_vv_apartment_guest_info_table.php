<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What a guest needs to get in and settle: door code, Wi-Fi, arrival
     * notes and the included cleaning rhythm. The host fills it in per
     * apartment; guests see the codes only shortly before check-in.
     */
    public function up(): void
    {
        Schema::create('vv_apartment_guest_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('apartment_id')->unique();
            $table->text('door_code')->nullable();
            $table->string('door_code_note', 255)->nullable();
            $table->text('wifi_network')->nullable();
            $table->text('wifi_password')->nullable();
            $table->text('arrival_instructions')->nullable();
            $table->text('parking_info')->nullable();
            $table->string('arrival_contact_label', 100)->nullable();
            $table->string('arrival_contact_phone', 30)->nullable();
            $table->string('security_phone', 30)->nullable();
            $table->unsignedTinyInteger('cleanings_per_week')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_apartment_guest_info');
    }
};
