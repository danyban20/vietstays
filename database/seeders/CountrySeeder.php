<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Creates the Vietnam country record and backfills it onto every existing
 * vv_cities row, since the legacy WordPress import has no country dimension
 * at all (city -> district only).
 */
class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $vietnam = Country::query()->updateOrCreate(
            ['name' => 'Vietnam'],
            ['code' => 'VN'],
        );

        DB::table('vv_cities')
            ->whereNull('country_id')
            ->update(['country_id' => $vietnam->id]);
    }
}
