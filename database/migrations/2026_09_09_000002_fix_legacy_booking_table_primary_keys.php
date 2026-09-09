<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 2026_06_24_061200_fix_vv_bookings_id_auto_increment runs (by timestamp)
     * BEFORE 2026_08_06_000002_create_vietstays_legacy_tables, which is what
     * actually creates vv_bookings/vv_booking_items/vv_apartment_availability_periods.
     * On any environment that ran migrations from empty (migrate:fresh, fresh
     * CI), the earlier migration is a silent no-op — the tables don't exist
     * yet — leaving these three with no primary key on their ID column. With
     * no unique constraint, re-seeding can (and did) insert duplicate rows.
     */
    public function up(): void
    {
        $this->fixPrimaryKey('vv_bookings', 'ID');
        $this->fixPrimaryKey('vv_booking_items', 'item_id');
        $this->fixPrimaryKey('vv_apartment_availability_periods', 'ID');
    }

    public function down(): void
    {
        // Repairs a missing constraint only — nothing meaningful to reverse.
    }

    protected function fixPrimaryKey(string $table, string $column): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $hasPrimaryKey = collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'"))->isNotEmpty();

        if ($hasPrimaryKey) {
            return;
        }

        $this->deduplicate($table, $column);

        $columnType = $this->columnType($table, $column);
        $maxId = (int) DB::table($table)->max($column);
        $nextId = max(1, $maxId + 1);

        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$columnType} NOT NULL AUTO_INCREMENT PRIMARY KEY");
        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$nextId}");
    }

    /**
     * Remove duplicate rows left behind by re-seeding while this table had no
     * unique constraint on $column, keeping the first-inserted copy of each.
     */
    protected function deduplicate(string $table, string $column): void
    {
        DB::statement("ALTER TABLE `{$table}` ADD COLUMN `_dedup_rowid` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY");

        DB::statement(
            "DELETE t1 FROM `{$table}` t1 ".
            "INNER JOIN `{$table}` t2 ON t1.`{$column}` = t2.`{$column}` AND t1.`_dedup_rowid` > t2.`_dedup_rowid`"
        );

        DB::statement("ALTER TABLE `{$table}` DROP COLUMN `_dedup_rowid`");
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
