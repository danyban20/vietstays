<?php

namespace Tests\Unit\Services;

use App\Models\Building;
use App\Models\District;
use App\Models\PricingMatrix;
use App\Models\DistrictPriceIndex;
use App\Models\BuildingPricingFactor;
use App\Services\PriceMatrixService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceMatrixServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PriceMatrixService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PriceMatrixService();

        // Seed pricing matrix
        PricingMatrix::create(['type_key' => 'Studio', 'base_price_vnd' => 900_000]);
        PricingMatrix::create(['type_key' => '1BR', 'base_price_vnd' => 1_150_000]);
        PricingMatrix::create(['type_key' => '2BR+1WC', 'base_price_vnd' => 1_500_000]);
        PricingMatrix::create(['type_key' => '2BR+2WC', 'base_price_vnd' => 1_900_000]);

        // Seed district indices
        DistrictPriceIndex::create(['district_code' => 'D1', 'district_name' => 'District 1', 'price_index' => 1.15]);
        DistrictPriceIndex::create(['district_code' => 'D2', 'district_name' => 'District 2', 'price_index' => 1.05]);
        DistrictPriceIndex::create(['district_code' => 'unknown', 'district_name' => 'Unknown', 'price_index' => 0.85]);
    }

    public function test_pmKey_studio_no_wc_variant()
    {
        $key = $this->service->pmKey('Studio');
        $this->assertEquals('Studio', $key);
    }

    public function test_pmKey_1br_no_wc_variant()
    {
        $key = $this->service->pmKey('1BR');
        $this->assertEquals('1BR', $key);
    }

    public function test_pmKey_with_default_wc()
    {
        $key = $this->service->pmKey('2BR');
        $this->assertEquals('2BR+1WC', $key);
    }

    public function test_pmKey_with_explicit_wc()
    {
        $key = $this->service->pmKey('2BR', 2);
        $this->assertEquals('2BR+2WC', $key);
    }

    public function test_calculate_default_matrix_price()
    {
        // Create district and building
        $district = District::create(['vv_id' => 1, 'district_code' => 'D1', 'name' => 'District 1']);
        $building = Building::create([
            'name' => 'Test Building',
            'district_id' => $district->id,
        ]);

        // Calculate price: 1,500,000 × 1.15 × factor
        $price = $this->service->calculateDefaultMatrixPrice($building, '2BR+1WC');

        // Should get 1,725,000 × factor (rounded to 50k)
        $this->assertIsInt($price);
        $this->assertEquals(0, $price % 50_000, 'Price should be rounded to 50k');
        $this->assertGreaterThan(1_500_000, $price); // factor > 1.0
        $this->assertLessThan(2_000_000, $price);
    }

    public function test_calculate_price_with_factor_override()
    {
        $district = District::create(['vv_id' => 2, 'district_code' => 'D2']);
        $building = Building::create([
            'name' => 'Test Building',
            'district_id' => $district->id,
        ]);

        // Set factor override
        BuildingPricingFactor::create([
            'building_id' => $building->id,
            'type_key' => '2BR+1WC',
            'factor_override' => 1.00,
        ]);

        $price = $this->service->calculateDefaultMatrixPrice($building, '2BR+1WC');

        // 1,500,000 × 1.05 × 1.00 = 1,575,000
        $this->assertEquals(1_575_000, $price);
    }

    public function test_calculate_price_with_absolute_override()
    {
        $district = District::create(['vv_id' => 3, 'district_code' => 'D1']);
        $building = Building::create([
            'name' => 'Premium Building',
            'district_id' => $district->id,
        ]);

        // Set absolute price override
        BuildingPricingFactor::create([
            'building_id' => $building->id,
            'type_key' => '2BR+1WC',
            'price_override_vnd' => 2_500_000,
        ]);

        $price = $this->service->calculateDefaultMatrixPrice($building, '2BR+1WC');

        // Should use override directly (rounded to 50k)
        $this->assertEquals(2_500_000, $price);
    }

    public function test_calculate_price_by_district()
    {
        $price = $this->service->calculateMatrixPriceByDistrict('D1', '2BR+1WC');

        // 1,500,000 × 1.15 = 1,725,000
        $this->assertEquals(1_725_000, $price);
    }

    public function test_calculate_price_by_district_d2()
    {
        $price = $this->service->calculateMatrixPriceByDistrict('D2', '2BR+1WC');

        // 1,500,000 × 1.05 = 1,575,000
        $this->assertEquals(1_575_000, $price);
    }

    public function test_get_all_type_keys()
    {
        $keys = $this->service->getAllTypeKeys();

        $this->assertContains('Studio', $keys);
        $this->assertContains('1BR', $keys);
        $this->assertContains('2BR+1WC', $keys);
        $this->assertContains('2BR+2WC', $keys);
        $this->assertCount(4, $keys);
    }

    public function test_get_all_base_prices()
    {
        $prices = $this->service->getAllBasePrices();

        $this->assertEquals(900_000, $prices['Studio']);
        $this->assertEquals(1_150_000, $prices['1BR']);
        $this->assertEquals(1_500_000, $prices['2BR+1WC']);
    }

    public function test_get_all_district_indices()
    {
        $indices = $this->service->getAllDistrictIndices();

        $this->assertEquals(1.15, $indices['D1']);
        $this->assertEquals(1.05, $indices['D2']);
        $this->assertEquals(0.85, $indices['unknown']);
    }

    public function test_price_is_rounded_to_50k()
    {
        // Create scenario with price that doesn't divide evenly by 50k
        $district = District::create(['vv_id' => 4, 'district_code' => 'D1']);
        $building = Building::create([
            'name' => 'Test',
            'district_id' => $district->id,
        ]);

        $price = $this->service->calculateDefaultMatrixPrice($building, '1BR');

        // All prices should end in 000 (multiple of 50k)
        $this->assertEquals(0, $price % 50_000);
    }
}
