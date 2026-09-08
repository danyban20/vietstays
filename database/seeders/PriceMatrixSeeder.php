<?php

namespace Database\Seeders;

use App\Models\PricingMatrix;
use App\Models\DistrictPriceIndex;
use Illuminate\Database\Seeder;

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

        // District price indices from spec
        $districtIndices = [
            ['D1', 'District 1', 1.15],
            ['D2', 'District 2', 1.05],
            ['D3', 'District 3', 1.05],
            ['D4', 'District 4', 0.90],
            ['D5', 'District 5', 0.88],
            ['D7', 'District 7', 1.00],
            ['D10', 'District 10', 0.90],
            ['BT', 'Binh Thanh', 0.95],
            ['PN', 'Phu Nhuan', 0.92],
            ['TB', 'Tan Binh', 0.88],
            ['TD', 'Thu Duc', 0.90],
            ['GV', 'Go Vap', 0.85],
            ['unknown', 'Unknown District', 0.85],
        ];

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
