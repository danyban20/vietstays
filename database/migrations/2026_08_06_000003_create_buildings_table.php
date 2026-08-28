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
        Schema::dropIfExists('buildings');

        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('district_wp_id');
            $table->integer('district_id')->nullable();
            $table->json('facilities')->nullable();
            $table->json('security_features')->nullable();
            $table->string('main_image')->nullable();
            $table->string('header_bg_image')->nullable();
            $table->text('header_text')->nullable();
            $table->json('building_gallery')->nullable();
            $table->string('status')->default('publish');
            $table->timestamps();

            $table->index('district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
