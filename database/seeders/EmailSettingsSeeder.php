<?php

namespace Database\Seeders;

use App\Models\VvOption;
use App\Support\LegacySqlParser;
use Illuminate\Database\Seeder;

class EmailSettingsSeeder extends Seeder
{
    /**
     * Seed email-related options from WordPress gyh_options.
     */
    public function run(): void
    {
        $parser = LegacySqlParser::default();

        $optionNames = [
            'vv-sending_receiving_settings',
            'vv-new_service_admin_email',
        ];

        $options = $parser->parseGyhOptions($optionNames);

        foreach ($optionNames as $name) {
            if (! array_key_exists($name, $options)) {
                $this->command?->warn("WordPress option [{$name}] not found in SQL dump.");

                continue;
            }

            VvOption::setValue($name, $options[$name] ?? '');
            $this->command?->info("Seeded option [{$name}].");
        }
    }
}
