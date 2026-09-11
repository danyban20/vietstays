<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vv_host_management_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->boolean('legal_registered')->default(false);
            $table->json('excluded')->nullable();
            $table->json('pending_invites')->nullable();
            $table->string('revenue_model', 20)->default('pool');
            $table->json('shares')->nullable();
            $table->unsignedSmallInteger('included_count')->default(0);
            $table->unsignedSmallInteger('company_apartment_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_host_management_companies');
    }
};
