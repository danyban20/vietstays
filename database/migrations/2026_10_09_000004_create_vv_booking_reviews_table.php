<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One review per finished booking. A row with skipped_at and no rating
     * records that the guest chose "Skip for now".
     */
    public function up(): void
    {
        Schema::create('vv_booking_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('booking_id')->unique();
            $table->unsignedInteger('apartment_id')->index();
            $table->unsignedBigInteger('member_user_id')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->json('category_ratings')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_booking_reviews');
    }
};
