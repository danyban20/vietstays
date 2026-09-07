<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vv_host_team_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('legacy_host_id')->nullable();
            $table->string('team_type', 20);
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('role');
            $table->string('area')->nullable();
            $table->json('permissions')->nullable();
            $table->unsignedTinyInteger('pay_rate')->nullable();
            $table->string('pay_setup', 20)->nullable();
            $table->string('org')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'team_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_host_team_invitations');
    }
};
