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
        Schema::create('vv_host_team_apartment_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_member_id');
            $table->unsignedInteger('apartment_id');
            $table->timestamps();

            $table->unique(['team_member_id', 'apartment_id'], 'team_apartment_assignments_unique');
            $table->index('apartment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vv_host_team_apartment_assignments');
    }
};
