<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vv_apartments')) {
            return;
        }

        $hasPrimaryKey = collect(DB::select('SHOW INDEX FROM `vv_apartments` WHERE Key_name = ?', ['PRIMARY']))
            ->isNotEmpty();

        if (! $hasPrimaryKey) {
            DB::statement('ALTER TABLE `vv_apartments` ADD PRIMARY KEY (`ID`)');
        }

        $maxId = (int) DB::table('vv_apartments')->max('ID');
        $nextId = max(1, $maxId + 1);

        DB::statement('ALTER TABLE `vv_apartments` MODIFY `ID` int NOT NULL AUTO_INCREMENT');
        DB::statement("ALTER TABLE `vv_apartments` AUTO_INCREMENT = {$nextId}");
    }

    public function down(): void
    {
        if (! Schema::hasTable('vv_apartments')) {
            return;
        }

        DB::statement('ALTER TABLE `vv_apartments` MODIFY `ID` int NOT NULL');
    }
};
