<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_matrices', function (Blueprint $table) {
            $table->id();
            $table->string('type_key'); // Studio, 1BR, 2BR+1WC, etc.
            $table->bigInteger('base_price_vnd'); // VND/night
            $table->timestamps();
            $table->unique('type_key');
        });

        Schema::create('district_price_indices', function (Blueprint $table) {
            $table->id();
            $table->string('district_code')->unique(); // D1, D2, D3, etc.
            $table->string('district_name'); // Full name
            $table->decimal('price_index', 4, 2); // 1.15, 1.05, etc.
            $table->timestamps();
        });

        Schema::create('building_pricing_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->cascadeOnDelete();
            $table->string('type_key'); // Studio, 1BR, 2BR+1WC, etc.
            $table->bigInteger('price_override_vnd')->nullable(); // Override absolute price (if set, this takes precedence)
            $table->decimal('factor_override', 3, 2)->nullable(); // Override factor (0.96-1.06 range)
            $table->timestamps();
            $table->unique(['building_id', 'type_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_pricing_factors');
        Schema::dropIfExists('district_price_indices');
        Schema::dropIfExists('pricing_matrices');
    }
};
