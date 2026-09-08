<?php

namespace App\Http\Controllers\Api;

use App\Models\Apartment;
use App\Models\Building;
use App\Services\PriceMatrixService;
use App\Services\BookingPricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingPriceCalculatorController
{
    protected PriceMatrixService $priceMatrixService;
    protected BookingPricingService $bookingPricingService;

    public function __construct(
        PriceMatrixService $priceMatrixService,
        BookingPricingService $bookingPricingService
    ) {
        $this->priceMatrixService = $priceMatrixService;
        $this->bookingPricingService = $bookingPricingService;
    }

    /**
     * Calculate price suggestion from matrix for an apartment
     * POST /api/bookings/calculate-price
     *
     * Required:
     * - apartment_id: int
     * - check_in: date (Y-m-d)
     * - check_out: date (Y-m-d)
     *
     * Returns:
     * - suggested_daily_price: from matrix (PM_BASE × district × factor)
     * - nights: number of nights
     * - room_total: suggested_daily_price × nights
     * - cleaning_fee: from apartment config
     * - total_before_fees: room_total + cleaning_fee
     * - booking_fee: platform fee (5%)
     * - total: grand total
     */
    public function calculatePrice(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|integer|exists:vv_apartments,ID',
            'check_in' => 'required|date_format:Y-m-d',
            'check_out' => 'required|date_format:Y-m-d|after:check_in',
        ]);

        try {
            $apartment = Apartment::findOrFail($validated['apartment_id']);
            $building = $apartment->building ?? null;

            if (!$building) {
                return response()->json([
                    'error' => 'Apartment has no associated building',
                    'apartment_id' => $validated['apartment_id'],
                ], 400);
            }

            $checkIn = Carbon::createFromFormat('Y-m-d', $validated['check_in']);
            $checkOut = Carbon::createFromFormat('Y-m-d', $validated['check_out']);
            $nights = $checkOut->diffInDays($checkIn);

            // Get apartment type key
            $typeKey = $this->getApartmentTypeKey($apartment);

            // Get suggested price from matrix
            $suggestedDailyPrice = $this->priceMatrixService->calculateDefaultMatrixPrice(
                $building,
                $typeKey
            );

            if ($suggestedDailyPrice === null) {
                return response()->json([
                    'error' => 'Could not calculate price for this apartment type',
                    'type_key' => $typeKey,
                ], 422);
            }

            // Get current stored price
            $currentDailyPrice = (int) $apartment->price_daily;

            // Get cleaning fee
            $cleaningFee = (int) ($apartment->cleaning_fee ?? 0);

            // Calculate totals
            $roomTotal = $suggestedDailyPrice * $nights;
            $totalBeforeFees = $roomTotal + $cleaningFee;
            $bookingFee = round($totalBeforeFees * 0.05); // 5% booking fee
            $suggestedTotal = $totalBeforeFees + $bookingFee;

            // Current booking total (if using stored price)
            $currentRoomTotal = $currentDailyPrice * $nights;
            $currentTotal = $currentRoomTotal + $cleaningFee + $bookingFee;

            return response()->json([
                'success' => true,
                'apartment_id' => $apartment->ID,
                'apartment_name' => $apartment->name,
                'building_name' => $building->name,
                'district' => $building->district?->name ?? 'Unknown',
                'building_id' => $building->id,
                'apartment_type' => $this->getApartmentTypeDisplay($apartment),
                'type_key' => $typeKey,
                
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d'),
                'nights' => $nights,
                
                // Matrix suggestion
                'suggested_daily_price' => $suggestedDailyPrice,
                'suggested_room_total' => $roomTotal,
                'suggested_total_before_fees' => $totalBeforeFees,
                'suggested_total' => $suggestedTotal,
                
                // Current stored price
                'current_daily_price' => $currentDailyPrice,
                'current_room_total' => $currentRoomTotal,
                'current_total' => $currentTotal,
                
                // Common values
                'cleaning_fee' => $cleaningFee,
                'booking_fee' => $bookingFee,
                
                // Difference
                'price_difference' => $suggestedDailyPrice - $currentDailyPrice,
                'price_difference_percent' => $currentDailyPrice > 0 
                    ? round(($suggestedDailyPrice - $currentDailyPrice) / $currentDailyPrice * 100, 2)
                    : 0,
                
                // Matrix breakdown (for transparency)
                'matrix' => [
                    'base_price' => $this->getPriceMatrixInfo($typeKey, $building),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Price calculation error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get apartment type key from type and WC count
     */
    private function getApartmentTypeKey(Apartment $apartment): string
    {
        // Try to extract type and wc from apartment name/type
        $type = $apartment->type ?? ''; // e.g., "2BR" or "Studio"
        $wc = $apartment->wc_count ?? null; // e.g., 1 or 2

        return $this->priceMatrixService->pmKey($type, $wc);
    }

    /**
     * Get human-friendly apartment type display
     */
    private function getApartmentTypeDisplay(Apartment $apartment): string
    {
        $type = $apartment->type ?? 'Unknown';
        $wc = $apartment->wc_count ?? null;

        if ($wc && !in_array($type, ['Studio', '1BR'])) {
            return "{$type} + {$wc} WC";
        }

        return $type;
    }

    /**
     * Get pricing matrix info for transparency
     */
    private function getPriceMatrixInfo(string $typeKey, Building $building): array
    {
        $basePrice = \App\Models\PricingMatrix::where('type_key', $typeKey)
            ->value('base_price_vnd');

        $districtCode = $building->district->district_code ?? 'unknown';
        $districtIndex = \App\Models\DistrictPriceIndex::where('district_code', $districtCode)
            ->value('price_index') ?? 0.85;

        $priceOverride = \App\Models\BuildingPricingFactor::where('building_id', $building->id)
            ->where('type_key', $typeKey)
            ->value('price_override_vnd');

        $factorOverride = \App\Models\BuildingPricingFactor::where('building_id', $building->id)
            ->where('type_key', $typeKey)
            ->value('factor_override');

        return [
            'type_key' => $typeKey,
            'base_price_vnd' => $basePrice,
            'district_code' => $districtCode,
            'district_index' => $districtIndex,
            'building_id' => $building->id,
            'price_override_vnd' => $priceOverride,
            'factor_override' => $factorOverride,
            'formula' => 'base_price × district_index × building_factor',
        ];
    }
}
