<?php

namespace App\Services;

use App\Models\Building;
use App\Models\PricingMatrix;
use App\Models\DistrictPriceIndex;
use App\Models\BuildingPricingFactor;

class PriceMatrixService
{
    // Type key defaults for bathroom count
    private const DEFAULT_WC = [
        'Studio' => 1,
        '1BR' => 1,
        '2BR' => 1,
        '3BR' => 2,
        '4BR' => 2,
        '5BR' => 3,
    ];

    /**
     * Generate type key from bedroom and bathroom count
     * Examples: Studio → "Studio", 1BR → "1BR", 2BR with 1 WC → "2BR+1WC"
     */
    public function pmKey(string $type, ?int $wcCount = null): string
    {
        // Studio and 1BR have no WC variant
        if (in_array($type, ['Studio', '1BR'])) {
            return $type;
        }

        // If no WC provided, use default
        if ($wcCount === null) {
            $wcCount = self::DEFAULT_WC[$type] ?? null;
        }

        if ($wcCount === null) {
            // Fallback to first existing key for that type
            return $this->getFirstExistingTypeKey($type);
        }

        return "{$type}+{$wcCount}WC";
    }

    /**
     * Get first existing type key for a given type
     */
    private function getFirstExistingTypeKey(string $type): string
    {
        $matrices = PricingMatrix::where('type_key', 'like', "{$type}%")
            ->orderBy('type_key')
            ->value('type_key');

        return $matrices ?? $type;
    }

    /**
     * Calculate default matrix price for apartment
     *
     * Formula: round50k(PM_BASE[typeKey] × PM_DISTRICT_INDEX[district] × buildingFactor)
     */
    public function calculateDefaultMatrixPrice(
        Building $building,
        string $typeKey
    ): ?int {
        // Get base price
        $basePrice = PricingMatrix::getBasePrice($typeKey);
        if ($basePrice === null) {
            return null;
        }

        // Get district index
        $districtCode = $building->district->district_code ?? null;
        $districtIndex = $districtCode
            ? DistrictPriceIndex::getPriceIndex($districtCode)
            : DistrictPriceIndex::getPriceIndex('unknown');

        if ($districtIndex === null) {
            $districtIndex = 0.85; // Default for unknown
        }

        // Check for price override (takes precedence)
        $priceOverride = BuildingPricingFactor::getPriceOverride(
            $building->id,
            $typeKey
        );
        if ($priceOverride !== null) {
            return $this->round50k($priceOverride);
        }

        // Check for factor override
        $factor = BuildingPricingFactor::getFactor($building->id, $typeKey);
        if ($factor === null) {
            // Default factor (deterministic hash of building name - 0.96 to 1.06)
            $factor = $this->getDefaultBuildingFactor($building->name);
        }

        // Calculate: base × district index × building factor
        $calculatedPrice = $basePrice * $districtIndex * $factor;

        return $this->round50k((int) round($calculatedPrice));
    }

    /**
     * Calculate default matrix price by district code and type
     * (without building factor - pure matrix value)
     */
    public function calculateMatrixPriceByDistrict(
        string $districtCode,
        string $typeKey
    ): ?int {
        $basePrice = PricingMatrix::getBasePrice($typeKey);
        if ($basePrice === null) {
            return null;
        }

        $districtIndex = DistrictPriceIndex::getPriceIndex($districtCode);
        if ($districtIndex === null) {
            $districtIndex = 0.85; // Default for unknown
        }

        $calculatedPrice = $basePrice * $districtIndex;
        return $this->round50k((int) round($calculatedPrice));
    }

    /**
     * Get deterministic building factor from hash of building name
     * Range: 0.96 to ~1.06 as per spec
     * Formula: 0.96 + (hash % 7)/58
     */
    private function getDefaultBuildingFactor(string $buildingName): float
    {
        $hash = crc32($buildingName) & 0x7FFFFFFF; // Ensure positive
        $hashMod = $hash % 7;
        return 0.96 + ($hashMod / 58);
    }

    /**
     * Round to nearest 50,000 VND
     */
    private function round50k(int $price): int
    {
        return round($price / 50000) * 50000;
    }

    /**
     * Get all available type keys from matrix
     */
    public function getAllTypeKeys(): array
    {
        return PricingMatrix::pluck('type_key')->toArray();
    }

    /**
     * Get all base prices
     */
    public function getAllBasePrices(): array
    {
        return PricingMatrix::getAllBasePrices();
    }

    /**
     * Get all district indices
     */
    public function getAllDistrictIndices(): array
    {
        return DistrictPriceIndex::getAllIndices();
    }
}
