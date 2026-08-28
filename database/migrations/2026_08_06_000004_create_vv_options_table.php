<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vv_options', function (Blueprint $table) {
            $table->id();
            $table->string('option_name', 191)->unique();
            $table->longText('option_value')->nullable();
            $table->string('autoload', 20)->default('auto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vv_options');
    }
};
