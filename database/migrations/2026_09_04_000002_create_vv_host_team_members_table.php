<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vv_host_team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('legacy_host_id')->nullable();
            $table->string('team_type', 20);
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('org')->nullable();
            $table->string('area')->nullable();
            $table->string('func')->nullable();
            $table->string('link', 20)->nullable();
            $table->string('member_type', 20)->nullable();
            $table->json('roles')->nullable();
            $table->json('permissions')->nullable();
            $table->unsignedSmallInteger('apartments')->default(0);
            $table->unsignedSmallInteger('bookings90')->default(0);
            $table->unsignedBigInteger('gross90')->default(0);
            $table->unsignedTinyInteger('out_pct')->default(0);
            $table->boolean('pooled')->default(false);
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedSmallInteger('tasks_week')->default(0);
            $table->string('avg_time', 20)->nullable();
            $table->boolean('avg_time_warn')->default(false);
            $table->boolean('guest_info')->default(false);
            $table->string('status', 20)->default('active');
            $table->string('avatar_color', 20)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'team_type']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_host_team_members');
    }
};
