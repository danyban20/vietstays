<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Owning host/partner, matches legacy_wp_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'email']);
        });

        Schema::table('vv_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('user_id');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('vv_bookings', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });

        Schema::dropIfExists('customers');
    }
};
