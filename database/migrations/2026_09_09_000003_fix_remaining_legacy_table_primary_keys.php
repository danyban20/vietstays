<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * None of these legacy vv_* tables (created verbatim from db/vietstays.sql's
     * CREATE TABLE statements) ever had a primary key. LegacyDataSeeder used to
     * get away with this by truncating each table before every re-import; once
     * it was changed to INSERT IGNORE (to stop wiping apartments/bookings
     * created through the app), tables with no unique constraint started
     * accumulating duplicate rows on every reseed instead of being protected
     * by it. This repairs the constraint and removes the duplicates that
     * already accumulated.
     */
    protected array $tables = [
        'vv_apartment_discounts' => 'apt_discount_id',
        'vv_apartment_foods' => 'apt_food_id',
        'vv_apartment_i18n' => 'ID',
        'vv_cleaners_checklists' => 'checklist_id',
        'vv_email_templates' => 'email_id',
        'vv_facilities' => 'facility_id',
        'vv_features' => 'feature_id',
        'vv_foods' => 'food_id',
        'vv_foods_categories' => 'food_category_id',
        'vv_host_applications' => 'ID',
        'vv_promocodes' => 'ID',
        'vv_rooms' => 'ID',
        'vv_security_features' => 'security_feature_id',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $column) {
            $this->fixPrimaryKey($table, $column);
        }
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
     * Remove duplicate rows, keeping the first-inserted copy of each $column value.
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
