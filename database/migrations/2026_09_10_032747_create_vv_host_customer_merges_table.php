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
        Schema::create('vv_host_customer_merges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('merged_key', 64);
            $table->string('keep_key', 64);
            $table->timestamps();

            $table->unique(['user_id', 'merged_key']);
            $table->index(['user_id', 'keep_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vv_host_customer_merges');
    }
};
