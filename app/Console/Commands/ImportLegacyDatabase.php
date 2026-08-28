<?php

namespace App\Console\Commands;

use App\Support\LegacySqlParser;
use Database\Seeders\BuildingSeeder;
use Database\Seeders\LegacyDataSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ImportLegacyDatabase extends Command
{
    /**
     * @var string
     */
    protected $signature = 'vietstays:import-legacy
                            {--skip-migrate : Skip running pending migrations}
                            {--only-data : Import data only, without running migrations}';

    /**
     * @var string
     */
    protected $description = 'One-shot import of Vietstays legacy database schema and seed data';

    public function handle(): int
    {
        $parser = LegacySqlParser::default();

        if (! is_readable($parser->sqlPath())) {
            $this->error('Legacy SQL dump not found at db/vietstays.sql');

            return self::FAILURE;
        }

        if (! $this->option('only-data') && ! $this->option('skip-migrate')) {
            $this->info('Running migrations...');
            Artisan::call('migrate', ['--force' => true], $this->output);
        }

        $this->info('Importing legacy vv_* table data...');
        $this->importLegacyTables($parser);

        $this->info('Importing cities and districts from WordPress...');
        (new LocationSeeder)->setCommand($this)->run();

        $this->info('Importing users...');
        (new UserSeeder)->setCommand($this)->run();

        $this->info('Importing buildings...');
        (new BuildingSeeder)->setCommand($this)->run();

        $this->info('Legacy import completed successfully.');

        return self::SUCCESS;
    }

    protected function importLegacyTables(LegacySqlParser $parser): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($parser->extractCreateTableStatements() as $table => $statement) {
            $statement = preg_replace(
                '/CREATE TABLE `'.preg_quote($table, '/').'`/',
                'CREATE TABLE IF NOT EXISTS `'.$table.'`',
                $statement,
                1,
            );

            DB::unprepared($statement);
        }

        (new LegacyDataSeeder)->setCommand($this)->run();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
