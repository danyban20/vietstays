<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vv_cities', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->nullable()->after('city_id');
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->index('country_id');
        });
    }

    public function down(): void
    {
        Schema::table('vv_cities', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
