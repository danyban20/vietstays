<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LegacyDataSeeder::class,
            LocationSeeder::class,
            DistrictCodeSeeder::class,
            UserSeeder::class,
            BuildingSeeder::class,
            PriceMatrixSeeder::class,
            CustomerBackfillSeeder::class,
            EmailSettingsSeeder::class,
        ]);
    }
}
