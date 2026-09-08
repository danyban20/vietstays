<?php

namespace App\Console\Commands;

use Database\Seeders\PriceMatrixSeeder;
use Illuminate\Console\Command;

class InitializePriceMatrix extends Command
{
    protected $signature = 'price-matrix:initialize';

    protected $description = 'Initialize price matrix with base prices and district indices';

    public function handle(): int
    {
        $this->info('Initializing price matrix...');

        $seeder = new PriceMatrixSeeder();
        $seeder->run();

        $this->info('✓ Price matrix initialized successfully!');
        $this->newLine();
        $this->info('Base prices set for 8 apartment types');
        $this->info('District indices set for 13 locations');

        return Command::SUCCESS;
    }
}
