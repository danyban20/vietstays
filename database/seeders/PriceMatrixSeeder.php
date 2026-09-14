<?php

namespace Database\Seeders;

use App\Models\PricingMatrix;
use App\Models\DistrictPriceIndex;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceMatrixSeeder extends Seeder
{
    public function run(): void
    {
        // Base prices from spec (VND/night)
        $basePrice = [
            'Studio' => 900_000,
            '1BR' => 1_150_000,
            '2BR+1WC' => 1_500_000,
            '2BR+2WC' => 1_900_000,
            '3BR+2WC' => 2_200_000,
            '4BR+2WC' => 3_300_000,
            '4BR+3WC' => 3_600_000,
            '5BR+3WC' => 4_400_000,
        ];

        foreach ($basePrice as $typeKey => $price) {
            PricingMatrix::updateOrCreate(
                ['type_key' => $typeKey],
                ['base_price_vnd' => $price]
            );
        }

        // District price indices from LOGIC-SPEC.md §2.1 PM_DISTRICT_INDEX. The
        // non-numbered districts are keyed by their full Vietnamese name (not an
        // abbreviation) because that's the exact district_code DistrictCodeSeeder
        // assigns — see memory "vietstays-district-code-gotcha": a mismatch here
        // silently drops these districts to the 'unknown' 0.85 index.
        $districtIndices = [
            ['D1', 'Quận 1', 1.15],
            ['D2', 'Quận 2', 1.05],
            ['D3', 'Quận 3', 1.05],
            ['D4', 'Quận 4', 0.90],
            ['D5', 'Quận 5', 0.88],
            ['D7', 'Quận 7', 1.00],
            ['D10', 'Quận 10', 0.90],
            ['Bình Thạnh', 'Bình Thạnh', 0.95],
            ['Phú Nhuận', 'Phú Nhuận', 0.92],
            ['Tân Bình', 'Tân Bình', 0.88],
            ['Thủ Đức', 'Thủ Đức', 0.90],
            ['Gò Vấp', 'Gò Vấp', 0.85],
            ['unknown', 'Unknown District', 0.85],
        ];

        DB::table('district_price_indices')
            ->whereIn('district_code', ['BT', 'PN', 'TB', 'TD', 'GV'])
            ->delete();

        foreach ($districtIndices as [$code, $name, $index]) {
            DistrictPriceIndex::updateOrCreate(
                ['district_code' => $code],
                [
                    'district_name' => $name,
                    'price_index' => $index,
                ]
            );
        }
    }
}
