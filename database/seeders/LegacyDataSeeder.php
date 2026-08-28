<?php

namespace Database\Seeders;

use App\Support\LegacySqlParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyDataSeeder extends Seeder
{
    /**
     * Seed legacy vv_* table data from the SQL dump.
     */
    public function run(): void
    {
        $parser = LegacySqlParser::default();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION'");

        foreach ($parser->extractInsertStatements('vv_') as $table => $statements) {
            if (in_array($table, ['vv_cities', 'vv_districts'], true)) {
                $this->command?->line("Skipped [{$table}] — seeded from WordPress via LocationSeeder.");

                continue;
            }

            DB::table($table)->truncate();

            foreach ($statements as $statement) {
                $statement = str_replace("'0000-00-00 00:00:00'", "'1970-01-01 00:00:00'", $statement);
                $statement = str_replace("'0000-00-00'", "'1970-01-01'", $statement);
                DB::unprepared($statement);
            }

            $this->command?->info("Imported data into [{$table}].");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
