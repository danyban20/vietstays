<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\ApartmentAvailabilityPeriod;
use App\Models\Booking;
use App\Models\Building;
use App\Models\District;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $windowStart = $request->filled('from')
            ? Carbon::parse($request->string('from'))->startOfDay()
            : now()->startOfDay()->subDays(2);

        $windowEnd = $request->filled('to')
            ? Carbon::parse($request->string('to'))->startOfDay()
            : $windowStart->copy()->addDays(14);

        if ($windowEnd->lte($windowStart)) {
            $windowEnd = $windowStart->copy()->addDays(14);
        }

        $apartmentQuery = Apartment::query()->orderBy('name');

        if ($request->user()->isOperator() && ! $request->user()->isAdmin()) {
            $apartmentQuery->where('user_id', $request->user()->legacy_wp_id);
        }

        $apartments = $apartmentQuery->get()->map(function (Apartment $apt) {
            $building = $apt->building_id ? Building::query()->find($apt->building_id) : null;
            $district = District::query()->find($apt->district);

            return [
                'id' => $apt->ID,
                'name' => $apt->display_name ?: $apt->name,
                'code' => 'VS'.str_pad((string) $apt->ID, 3, '0', STR_PAD_LEFT),
                'district' => $district?->name,
                'building' => $building?->name,
                'type' => $apt->apartment_type,
            ];
        });

        $apartmentIds = $apartments->pluck('id')->all();

        $bookings = Booking::query()
            ->whereIn('apartment_id', $apartmentIds)
            ->whereDate('check_out_date', '>', $windowStart)
            ->whereDate('check_in_date', '<', $windowEnd)
            ->orderBy('check_in_date')
            ->get();

        $periods = ApartmentAvailabilityPeriod::query()
            ->whereIn('apartment_id', $apartmentIds)
            ->whereDate('end_date', '>=', $windowStart)
            ->whereDate('start_date', '<', $windowEnd)
            ->orderBy('start_date')
            ->get();

        $items = collect();

        foreach ($bookings as $booking) {
            $extra = is_array($booking->extra_data) ? $booking->extra_data : [];
            $channel = strtolower((string) ($extra['source'] ?? 'vietstays'));
            $type = 'vietstays';

            if (str_contains($channel, 'airbnb')) {
                $type = 'airbnb';
            } elseif (in_array($channel, ['booking.com', 'trip.com', 'external'], true)) {
                $type = 'external';
            }

            $checkIn = $booking->check_in_date->copy()->startOfDay();
            $checkOut = $booking->check_out_date->copy()->startOfDay();
            $nights = max(1, $checkIn->diffInDays($checkOut));

            $items->push([
                'id' => 'booking-'.$booking->ID,
                'booking_id' => $booking->ID,
                'apartment_id' => (int) $booking->apartment_id,
                'type' => $type,
                'guest' => trim($booking->firstname.' '.$booking->lastname) ?: 'Guest',
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'nights' => $nights,
                'draggable' => true,
            ]);
        }

        foreach ($periods as $period) {
            $type = $period->period_type === 'manual_block' ? 'blocked' : 'external';
            $start = $period->start_date->copy()->startOfDay();
            $end = $period->end_date->copy()->startOfDay()->addDay();

            if ($type === 'external') {
                $source = strtolower((string) ($period->external_platform ?: $period->source ?: ''));
                if (str_contains($source, 'airbnb')) {
                    $type = 'airbnb';
                }
            }

            $guest = $type === 'blocked'
                ? ($period->note ?: 'Blocked')
                : ($period->guest_name ?: 'External guest');

            $items->push([
                'id' => 'period-'.$period->ID,
                'period_id' => $period->ID,
                'apartment_id' => (int) $period->apartment_id,
                'type' => $type,
                'guest' => $guest,
                'check_in' => $start->toDateString(),
                'check_out' => $end->toDateString(),
                'nights' => max(1, $start->diffInDays($end)),
                'draggable' => false,
            ]);
        }

        return response()->json([
            'data' => [
                'window_start' => $windowStart->toDateString(),
                'window_end' => $windowEnd->toDateString(),
                'apartments' => $apartments->values(),
                'items' => $items->values(),
            ],
        ]);
    }
}
