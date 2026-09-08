<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tags districts with the price-matrix code from LOGIC-SPEC.md §2.1 (PM_DISTRICT_INDEX)
 * and creates any of those districts missing from the legacy WordPress import, so every
 * priced district is selectable when adding an apartment.
 */
class DistrictCodeSeeder extends Seeder
{
    private const HCMC_CITY_ID = 440;

    private const SYNTHETIC_ID_BASE = 900_001;

    /**
     * Spec district code => canonical display name.
     */
    private const DISTRICTS = [
        'D1' => 'Quận 1',
        'D2' => 'Quận 2',
        'D3' => 'Quận 3',
        'D4' => 'Quận 4',
        'D5' => 'Quận 5',
        'D7' => 'Quận 7',
        'D10' => 'Quận 10',
        'Bình Thạnh' => 'Bình Thạnh',
        'Phú Nhuận' => 'Phú Nhuận',
        'Tân Bình' => 'Tân Bình',
        'Thủ Đức' => 'Thủ Đức',
        'Gò Vấp' => 'Gò Vấp',
    ];

    /**
     * Legacy district names (as imported from WordPress) mapped to their spec code.
     */
    private const LEGACY_NAME_ALIASES = [
        'Quận 1' => 'D1',
        'District 1' => 'D1',
        'Quận 2' => 'D2',
        'District 2' => 'D2',
        'Quận 4' => 'D4',
        'District 4' => 'D4',
    ];

    public function run(): void
    {
        $tagged = 0;
        foreach (self::LEGACY_NAME_ALIASES as $legacyName => $code) {
            $tagged += DB::table('vv_districts')
                ->where('name', $legacyName)
                ->update(['district_code' => $code]);
        }

        $existingCodes = DB::table('vv_districts')
            ->whereNotNull('district_code')
            ->pluck('district_code')
            ->all();

        $nextId = self::SYNTHETIC_ID_BASE;
        $created = 0;
        $now = now()->timestamp;

        foreach (self::DISTRICTS as $code => $name) {
            if (in_array($code, $existingCodes, true)) {
                continue;
            }

            DB::table('vv_districts')->insert([
                'district_id' => $nextId++,
                'district_num' => Str::limit(Str::slug($name), 20, ''),
                'name' => $name,
                'city_id' => self::HCMC_CITY_ID,
                'district_code' => $code,
                'dateadded' => $now,
                'datemodified' => $now,
            ]);

            $created++;
        }

        $this->command?->info("Tagged {$tagged} legacy districts and created {$created} missing districts with price-matrix codes.");
    }
}
