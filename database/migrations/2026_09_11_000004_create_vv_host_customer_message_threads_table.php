<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vv_host_customer_message_threads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('customer_key', 64);
            $table->string('guest_token', 64)->unique();
            $table->timestamp('host_last_read_at', 6)->nullable();
            $table->timestamp('last_message_at', 6)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'customer_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_host_customer_message_threads');
    }
};
