<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\ApartmentAvailabilityPeriod;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApartmentAvailabilityController extends Controller
{
    public function index(Request $request, int $apartment): JsonResponse
    {
        $model = Apartment::query()->findOrFail($apartment);
        $this->authorizeApartment($request, $model);

        $rangeStart = $request->filled('from')
            ? Carbon::parse($request->string('from'))->startOfDay()
            : now()->startOfDay()->subMonth();

        $rangeEnd = $request->filled('to')
            ? Carbon::parse($request->string('to'))->startOfDay()
            : now()->startOfDay()->addMonths(4);

        $periods = collect();

        $bookings = Booking::query()
            ->where('apartment_id', $model->ID)
            ->whereDate('check_out_date', '>', $rangeStart)
            ->whereDate('check_in_date', '<', $rangeEnd)
            ->orderBy('check_in_date')
            ->get();

        foreach ($bookings as $booking) {
            $periods->push($this->transformBookingPeriod($booking, $model));
        }

        $blocks = ApartmentAvailabilityPeriod::query()
            ->where('apartment_id', $model->ID)
            ->whereDate('end_date', '>=', $rangeStart)
            ->whereDate('start_date', '<', $rangeEnd)
            ->orderBy('start_date')
            ->get();

        foreach ($blocks as $block) {
            $periods->push($this->transformAvailabilityPeriod($block));
        }

        $sorted = $periods
            ->sortBy(fn (array $p) => $p['start_date'])
            ->values();

        $today = now()->startOfDay();
        $active = $sorted->first(function (array $period) use ($today) {
            $start = Carbon::parse($period['start_date']);
            $end = Carbon::parse($period['end_date']);

            return $start->lte($today) && $end->gt($today) && $period['period_type'] !== 'blocked';
        });

        return response()->json([
            'data' => [
                'periods' => $sorted,
                'active_period_id' => $active['id'] ?? null,
                'live_status' => $this->liveStatus($sorted, $today),
            ],
        ]);
    }

    public function destroy(Request $request, int $apartment, int $period): JsonResponse
    {
        $model = Apartment::query()->findOrFail($apartment);
        $this->authorizeApartment($request, $model);

        $row = ApartmentAvailabilityPeriod::query()
            ->where('apartment_id', $model->ID)
            ->where('ID', $period)
            ->firstOrFail();

        $row->delete();

        return response()->json(['message' => 'Period removed.']);
    }

    public function update(Request $request, int $apartment, int $period): JsonResponse
    {
        $model = Apartment::query()->findOrFail($apartment);
        $this->authorizeApartment($request, $model);

        $row = ApartmentAvailabilityPeriod::query()
            ->where('apartment_id', $model->ID)
            ->where('ID', $period)
            ->firstOrFail();

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $row->update([
            'note' => $validated['note'] ?? '',
            'datemodified' => now(),
        ]);

        return response()->json([
            'data' => $this->transformAvailabilityPeriod($row->fresh()),
            'message' => 'Period updated.',
        ]);
    }

    protected function transformBookingPeriod(Booking $booking, Apartment $apartment): array
    {
        $checkIn = $booking->check_in_date->copy()->startOfDay();
        $checkOut = $booking->check_out_date->copy()->startOfDay();
        $nights = max(1, $checkIn->diffInDays($checkOut));
        $extra = is_array($booking->extra_data) ? $booking->extra_data : [];
        $guest = trim($booking->firstname.' '.$booking->lastname) ?: 'Guest';

        $dailyRate = (float) $booking->price;
        $cleaningFee = (float) ($apartment->cleaning_fee ?? 0);
        $roomTotal = $dailyRate * $nights;
        $discount = (float) $booking->campaign_discount;
        $gmv = max(0, $roomTotal + $cleaningFee - $discount);
        $platformFee = round($gmv * 0.05);
        $cashPoints = round($gmv * 0.03);
        $hostNet = $gmv - $platformFee - $cashPoints;

        $channel = strtolower((string) ($extra['source'] ?? 'vietstays'));
        $periodType = 'vietstays';
        if (str_contains($channel, 'airbnb')) {
            $periodType = 'external';
        } elseif (in_array($channel, ['booking.com', 'trip.com', 'external'], true)) {
            $periodType = 'external';
        }

        return [
            'id' => 'booking-'.$booking->ID,
            'source_type' => 'booking',
            'source_id' => $booking->ID,
            'period_type' => $periodType,
            'start_date' => $checkIn->toDateString(),
            'end_date' => $checkOut->toDateString(),
            'nights' => $nights,
            'label' => $guest,
            'subtitle' => $this->channelLabel($periodType, $booking->promo_code),
            'status' => $booking->status,
            'stay' => [
                'guest' => $guest,
                'nights' => $nights,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'guests' => (int) $booking->adults + (int) $booking->children,
            ],
            'extras' => [
                'cleaning' => $cleaningFee,
                'extra_cleaning' => null,
                'discount_code' => $booking->promo_code ?: null,
                'commission' => $extra['discount_type'] === 'host_agent' ? 'Host agent' : null,
            ],
            'channel' => [
                'name' => $this->channelLabel($periodType, $booking->promo_code),
                'reference' => $booking->booking_num,
                'registered_by' => 'Vietstays',
            ],
            'note' => '',
            'finance' => [
                'nightly_rate' => $dailyRate,
                'room_total' => $roomTotal,
                'cleaning' => $cleaningFee,
                'discount' => $discount,
                'gmv' => $gmv,
                'platform_fee' => $platformFee,
                'cash_points' => $cashPoints,
                'host_net' => $hostNet,
                'vietstays_net' => $platformFee - $cashPoints,
            ],
        ];
    }

    protected function transformAvailabilityPeriod(ApartmentAvailabilityPeriod $period): array
    {
        $start = $period->start_date->copy()->startOfDay();
        $end = $period->end_date->copy()->startOfDay()->addDay();
        $nights = max(1, $start->diffInDays($end));
        $isBlock = $period->period_type === 'manual_block';
        $periodType = $isBlock ? 'blocked' : 'external';

        $source = strtolower((string) ($period->external_platform ?: $period->source ?: ''));
        if (! $isBlock && str_contains($source, 'airbnb')) {
            $periodType = 'external';
        }

        $label = $isBlock
            ? ($period->note ?: 'Blocked dates')
            : ($period->guest_name ?: 'External guest');

        return [
            'id' => 'period-'.$period->ID,
            'source_type' => 'period',
            'source_id' => $period->ID,
            'period_type' => $periodType,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'nights' => $nights,
            'label' => $label,
            'subtitle' => $isBlock ? 'Manual block' : ($period->external_platform ?: 'External booking'),
            'status' => $isBlock ? 'blocked' : 'external',
            'stay' => [
                'guest' => $period->guest_name ?: ($isBlock ? '—' : 'External guest'),
                'nights' => $nights,
                'check_in' => $start->toDateString(),
                'check_out' => $end->toDateString(),
                'guests' => null,
            ],
            'extras' => [
                'cleaning' => null,
                'extra_cleaning' => null,
                'discount_code' => null,
                'commission' => null,
            ],
            'channel' => [
                'name' => $isBlock ? 'Manual block' : ($period->external_platform ?: 'External'),
                'reference' => $period->external_booking_ref ?: '',
                'registered_by' => $period->source ?: 'External',
            ],
            'note' => $period->note ?? '',
            'finance' => null,
            'block_reason' => $isBlock ? ($period->note ?: '') : null,
        ];
    }

    protected function channelLabel(string $periodType, ?string $promoCode): string
    {
        return match ($periodType) {
            'blocked' => 'Manual block',
            'external' => 'External booking',
            default => $promoCode ? 'Vietstays · '.$promoCode : 'Vietstays direct booking',
        };
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $periods
     * @return array{state: string, label: string}
     */
    protected function liveStatus($periods, Carbon $today): array
    {
        foreach ($periods as $period) {
            $start = Carbon::parse($period['start_date']);
            $end = Carbon::parse($period['end_date']);

            if ($start->lte($today) && $end->gt($today)) {
                if ($period['period_type'] === 'blocked') {
                    return [
                        'state' => 'blocked',
                        'label' => 'Blocked — '.$period['label'],
                    ];
                }

                return [
                    'state' => 'occupied',
                    'label' => 'Guest in stay — '.$period['label'],
                ];
            }
        }

        $upcoming = $periods->first(function (array $period) use ($today) {
            return Carbon::parse($period['start_date'])->gt($today);
        });

        if ($upcoming) {
            return [
                'state' => 'upcoming',
                'label' => 'Next: '.$upcoming['label'].' · '.$upcoming['start_date'],
            ];
        }

        return [
            'state' => 'vacant',
            'label' => 'Vacant — no active booking',
        ];
    }

    protected function authorizeApartment(Request $request, Apartment $apartment): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isPartner() && (int) $apartment->user_id === (int) $user->legacy_wp_id) {
            return;
        }

        abort(403);
    }
}
