<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->fixAutoIncrement('vv_bookings', 'ID');
        $this->fixAutoIncrement('vv_booking_items', 'item_id');
        $this->fixAutoIncrement('vv_apartment_availability_periods', 'ID');
    }

    public function down(): void
    {
        if (Schema::hasTable('vv_bookings')) {
            DB::statement('ALTER TABLE `vv_bookings` MODIFY `ID` int NOT NULL');
        }

        if (Schema::hasTable('vv_booking_items')) {
            DB::statement('ALTER TABLE `vv_booking_items` MODIFY `item_id` int NOT NULL');
        }

        if (Schema::hasTable('vv_apartment_availability_periods')) {
            DB::statement('ALTER TABLE `vv_apartment_availability_periods` MODIFY `ID` bigint unsigned NOT NULL');
        }
    }

    protected function fixAutoIncrement(string $table, string $column): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $hasPrimaryKey = collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", ['PRIMARY']))
            ->isNotEmpty();

        if (! $hasPrimaryKey) {
            DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$column}`)");
        }

        $maxId = (int) DB::table($table)->max($column);
        $nextId = max(1, $maxId + 1);

        $columnType = $this->columnType($table, $column);

        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$columnType} NOT NULL AUTO_INCREMENT");
        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$nextId}");
    }

    protected function columnType(string $table, string $column): string
    {
        $row = collect(DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]))->first();

        if (! $row) {
            return 'int';
        }

        $type = strtolower((string) $row->Type);

        if (str_contains($type, 'bigint')) {
            return str_contains($type, 'unsigned') ? 'bigint unsigned' : 'bigint';
        }

        return 'int';
    }
};
