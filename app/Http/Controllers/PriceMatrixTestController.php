<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\PricingMatrix;
use App\Models\DistrictPriceIndex;
use App\Models\BuildingPricingFactor;
use App\Services\PriceMatrixService;

class PriceMatrixTestController extends Controller
{
    protected PriceMatrixService $priceService;

    public function __construct()
    {
        $this->priceService = new PriceMatrixService();
    }

    /**
     * Show price matrix test page
     */
    public function index()
    {
        $buildings = Building::with('district')->limit(10)->get();
        $typeKeys = $this->priceService->getAllTypeKeys();
        $basePrices = $this->priceService->getAllBasePrices();
        $districtIndices = $this->priceService->getAllDistrictIndices();

        return view('price-matrix-test.index', compact(
            'buildings',
            'typeKeys',
            'basePrices',
            'districtIndices'
        ));
    }

    /**
     * Calculate price for a building and type
     */
    public function calculate()
    {
        $buildingId = request('building_id');
        $typeKey = request('type_key');

        if (!$buildingId || !$typeKey) {
            return response()->json(['error' => 'Missing parameters'], 422);
        }

        $building = Building::with('district')->find($buildingId);
        if (!$building) {
            return response()->json(['error' => 'Building not found'], 404);
        }

        try {
            $price = $this->priceService->calculateDefaultMatrixPrice($building, $typeKey);

            if ($price === null) {
                return response()->json(['error' => 'Type key not found'], 404);
            }

            // Get base price and district index for reference
            $basePrice = PricingMatrix::where('type_key', $typeKey)->value('base_price_vnd');
            $districtCode = $building->district?->district_code ?? 'unknown';
            $districtIndex = DistrictPriceIndex::where('district_code', $districtCode)->value('price_index') ?? 0.85;

            // Check for building overrides
            $priceOverride = BuildingPricingFactor::where('building_id', $buildingId)
                ->where('type_key', $typeKey)
                ->value('price_override_vnd');
            $factorOverride = BuildingPricingFactor::where('building_id', $buildingId)
                ->where('type_key', $typeKey)
                ->value('factor_override');

            return response()->json([
                'success' => true,
                'building_id' => $buildingId,
                'building_name' => $building->name,
                'district' => $building->district?->name ?? 'Unknown',
                'district_code' => $districtCode,
                'type_key' => $typeKey,
                'base_price' => $basePrice,
                'district_index' => $districtIndex,
                'price_override' => $priceOverride,
                'factor_override' => $factorOverride,
                'calculated_price' => number_format($price, 0, ',', '.'),
                'calculated_price_raw' => $price,
                'formula' => "round50k(base_price × district_index × building_factor)",
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all buildings with their info
     */
    public function buildings()
    {
        $buildings = Building::with('district')
            ->select('id', 'name', 'district_id')
            ->limit(50)
            ->get()
            ->map(function ($building) {
                return [
                    'id' => $building->id,
                    'name' => $building->name,
                    'district' => $building->district?->name ?? 'Unknown',
                ];
            });

        return response()->json($buildings);
    }
}
