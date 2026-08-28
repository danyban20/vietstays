<?php

namespace App\Services;

use App\Models\Apartment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class BookingPricingService
{
    public function quote(
        Apartment $apartment,
        Carbon $checkIn,
        Carbon $checkOut,
        array $options = [],
    ): array {
        $checkIn = $checkIn->copy()->startOfDay();
        $checkOut = $checkOut->copy()->startOfDay();

        if ($checkOut->lte($checkIn)) {
            throw new \InvalidArgumentException('Check-out must be after check-in.');
        }

        $nights = max(1, $checkIn->diffInDays($checkOut));
        $pricing = is_array($apartment->pricing) ? $apartment->pricing : [];
        $discounts = $this->loadCampaignDiscounts((int) $apartment->ID);

        $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());
        $dailyPrices = [];
        $roomTotal = 0.0;
        $campaignDiscount = 0.0;
        $campaignDiscountDesc = [];

        foreach ($period as $date) {
            $cost = (float) $apartment->price_daily;
            $dayOfWeek = (int) $date->format('w');

            if ($dayOfWeek === 5 || $dayOfWeek === 6) {
                $addon = (float) ($pricing['addon_days2'] ?? 0);
                if ($addon > 0) {
                    $cost += $cost * ($addon / 100);
                }
            }

            $dailyPrices[$date->format('Y-m-d')] = $cost;
            $roomTotal += $cost;

            $dateStr = $date->format('Y-m-d');
            $dayCampaignPercent = 0.0;

            foreach ($discounts as $discount) {
                $dateStart = $this->normalizeDiscountDate($discount['datestart'] ?? null);
                $dateEnd = $this->normalizeDiscountDate($discount['dateend'] ?? null, farFuture: true);

                if ($dateStart && $dateStr >= $dateStart && $dateStr <= $dateEnd) {
                    $value = (float) ($discount['discount'] ?? 0);
                    if ($value > 0 && ($dayCampaignPercent === 0.0 || $value < $dayCampaignPercent)) {
                        $dayCampaignPercent = $value;
                    }
                }
            }

            if ($dayCampaignPercent > 0) {
                $amount = $cost * ($dayCampaignPercent / 100);
                $campaignDiscount += $amount;
                $campaignDiscountDesc[] = [
                    'date' => $dateStr,
                    'discount' => $dayCampaignPercent,
                ];
            }
        }

        $basicDiscountPercent = $this->extendedStayDiscountPercent($nights, $pricing);
        $subtotalAfterCampaign = max(0, $roomTotal - $campaignDiscount);
        $basicDiscountAmount = $basicDiscountPercent > 0
            ? round($subtotalAfterCampaign * ($basicDiscountPercent / 100))
            : 0.0;

        $subtotalAfterBasic = max(0, $subtotalAfterCampaign - $basicDiscountAmount);

        $promoCodeDiscountPercent = (float) ($options['promo_code_discount_percent'] ?? 0);
        $promoCode = trim((string) ($options['promo_code'] ?? ''));
        $promoDiscountAmount = 0.0;

        if ($promoCode !== '' && $promoCodeDiscountPercent > 0 && $campaignDiscount <= 0) {
            $promoDiscountAmount = round($subtotalAfterBasic * ($promoCodeDiscountPercent / 100));
        }

        $subtotalAfterPromo = max(0, $subtotalAfterBasic - $promoDiscountAmount);

        $bookingFeePercent = (float) config('vietstays.booking_fee_percent', 5);
        $bookingFee = $bookingFeePercent > 0
            ? round($subtotalAfterPromo * ($bookingFeePercent / 100))
            : 0.0;

        $cleaningFee = (float) ($apartment->cleaning_fee ?? 0);
        $numExtraCleaning = max(0, (int) ($options['num_cleaning'] ?? 0));
        $extraCleaningTotal = $numExtraCleaning > 0 ? $cleaningFee * $numExtraCleaning : 0.0;

        $airportPickup = ! empty($options['airport_pickup']);
        $airportPickupCost = $airportPickup
            ? (float) config('vietstays.airport_pickup_cost', 0)
            : 0.0;

        $total = $subtotalAfterPromo + $bookingFee + $cleaningFee + $extraCleaningTotal + $airportPickupCost;

        $rateLines = $this->buildRateLines($dailyPrices);

        return [
            'available' => true,
            'nights' => $nights,
            'label' => $this->dateRangeLabel($checkIn, $checkOut, $nights),
            'currency' => config('vietstays.site_currency', 'VND'),
            'room_total' => round($roomTotal, 2),
            'rate_lines' => $rateLines,
            'campaign_discount' => round($campaignDiscount, 2),
            'campaign_discount_desc' => $campaignDiscountDesc,
            'basic_discount_percent' => $basicDiscountPercent,
            'basic_discount_amount' => round($basicDiscountAmount, 2),
            'promo_code' => $promoCode,
            'promo_code_discount_percent' => $promoCodeDiscountPercent,
            'promo_discount_amount' => round($promoDiscountAmount, 2),
            'booking_fee_percent' => $bookingFeePercent,
            'booking_fee' => round($bookingFee, 2),
            'cleaning_fee' => round($cleaningFee, 2),
            'num_cleaning' => $numExtraCleaning,
            'extra_cleaning_total' => round($extraCleaningTotal, 2),
            'airport_pickup' => $airportPickup,
            'airport_pickup_cost' => round($airportPickupCost, 2),
            'total' => round($total, 2),
            'payment_method' => $options['payment_method'] ?? 'onsite',
        ];
    }

    /**
     * @return array<int, array{start: string, end: string, price: float, nights: int}>
     */
    protected function buildRateLines(array $dailyPrices): array
    {
        if ($dailyPrices === []) {
            return [];
        }

        $result = [];
        $startDate = null;
        $prevDate = null;
        $prevPrice = null;

        foreach ($dailyPrices as $date => $price) {
            $price = (float) $price;

            if ($startDate === null) {
                $startDate = $date;
            }

            if ($prevPrice !== null) {
                $expectedNext = Carbon::parse($prevDate)->addDay()->format('Y-m-d');
                if ($price !== $prevPrice || $date !== $expectedNext) {
                    $start = Carbon::parse($startDate);
                    $end = Carbon::parse($prevDate)->addDay();
                    $result[] = [
                        'start' => $startDate,
                        'end' => $end->format('Y-m-d'),
                        'price' => $prevPrice,
                        'nights' => max(1, $start->diffInDays($end)),
                    ];
                    $startDate = $date;
                }
            }

            $prevPrice = $price;
            $prevDate = $date;
        }

        if ($prevDate !== null) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($prevDate)->addDay();
            $result[] = [
                'start' => $startDate,
                'end' => $end->format('Y-m-d'),
                'price' => (float) $prevPrice,
                'nights' => max(1, $start->diffInDays($end)),
            ];
        }

        return $result;
    }

    protected function extendedStayDiscountPercent(int $nights, array $pricing): float
    {
        if ($nights >= 30) {
            return (float) ($pricing['discount_30days'] ?? 0);
        }

        if ($nights >= 7) {
            return (float) ($pricing['discount_7days'] ?? 0);
        }

        if ($nights >= 5) {
            return (float) ($pricing['discount_5days'] ?? 0);
        }

        if ($nights >= 3) {
            return (float) ($pricing['discount_3days'] ?? 0);
        }

        return 0.0;
    }

    protected function dateRangeLabel(Carbon $checkIn, Carbon $checkOut, int $nights): string
    {
        if ($checkIn->format('Y') !== $checkOut->format('Y')) {
            $label = $checkIn->format('j M Y').' – '.$checkOut->format('j M Y');
        } else {
            $label = $checkIn->format('j M').' – '.$checkOut->format('j M');
        }

        $label .= ' ('.$nights.' '.($nights === 1 ? 'night' : 'nights').')';

        return $label;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function loadCampaignDiscounts(int $apartmentId): array
    {
        return DB::table('vv_apartment_discounts')
            ->where('apartment_id', $apartmentId)
            ->orderByDesc('datestart')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    protected function normalizeDiscountDate(mixed $value, bool $farFuture = false): ?string
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return $farFuture ? now()->addYears(100)->format('Y-m-d') : null;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value)->format('Y-m-d');
        }

        return Carbon::parse((string) $value)->format('Y-m-d');
    }
}
