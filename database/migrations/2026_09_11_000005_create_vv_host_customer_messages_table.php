<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vv_host_customer_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id');
            $table->unsignedInteger('booking_id')->nullable();
            $table->string('sender', 10);
            $table->string('author')->nullable();
            $table->text('body');
            $table->timestamp('created_at', 6)->useCurrent();

            $table->index(['thread_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_host_customer_messages');
    }
};
