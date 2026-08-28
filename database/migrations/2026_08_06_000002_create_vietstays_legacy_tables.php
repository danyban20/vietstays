<?php

use App\Support\LegacySqlParser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    protected array $legacyTables = [];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            throw new \RuntimeException('Vietstays legacy tables require MySQL. Set DB_CONNECTION=mysql in .env');
        }

        $parser = LegacySqlParser::default();
        $this->legacyTables = $parser->extractCreateTableNames();

        foreach ($parser->extractCreateTableStatements() as $table => $statement) {
            $statement = preg_replace(
                '/CREATE TABLE `'.preg_quote($table, '/').'`/',
                'CREATE TABLE IF NOT EXISTS `'.$table.'`',
                $statement,
                1,
            );

            DB::unprepared($statement);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->legacyTables === []) {
            $this->legacyTables = LegacySqlParser::default()->extractCreateTableNames();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (array_reverse($this->legacyTables) as $table) {
            DB::statement("DROP TABLE IF EXISTS `{$table}`");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
