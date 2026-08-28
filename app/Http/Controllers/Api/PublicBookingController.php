<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Services\BookingAvailabilityService;
use App\Services\BookingConfirmationEmailService;
use App\Services\BookingCreationService;
use App\Services\BookingPricingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicBookingController extends Controller
{
    public function __construct(
        protected BookingPricingService $pricingService,
        protected BookingAvailabilityService $availabilityService,
        protected BookingCreationService $bookingService,
        protected BookingConfirmationEmailService $confirmationEmailService,
    ) {}

    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'apartment_id' => ['required', 'integer'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'num_cleaning' => ['nullable', 'integer', 'min:0'],
            'airport_pickup' => ['nullable', 'boolean'],
            'promo_code' => ['nullable', 'string', 'max:20'],
            'promo_code_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_method' => ['nullable', Rule::in(['onsite', 'card'])],
        ]);

        $apartment = Apartment::query()
            ->where('status', 'active')
            ->findOrFail($validated['apartment_id']);

        $checkIn = Carbon::parse($validated['check_in_date'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out_date'])->startOfDay();

        $adults = max(1, (int) ($validated['adults'] ?? 1));
        $children = max(0, (int) ($validated['children'] ?? 0));
        $totalGuests = $adults + $children;

        if ($totalGuests > (int) ($apartment->max_guests ?? 0)) {
            return response()->json([
                'message' => 'This apartment allows a maximum of '.(int) $apartment->max_guests.' guest(s).',
            ], 422);
        }

        if (! $this->availabilityService->isAvailable($apartment->ID, $checkIn, $checkOut)) {
            return response()->json([
                'message' => 'Selected dates are unavailable.',
                'available' => false,
            ], 422);
        }

        try {
            $quote = $this->pricingService->quote($apartment, $checkIn, $checkOut, [
                'num_cleaning' => (int) ($validated['num_cleaning'] ?? 0),
                'airport_pickup' => ! empty($validated['airport_pickup']),
                'promo_code' => $validated['promo_code'] ?? '',
                'promo_code_discount_percent' => (float) ($validated['promo_code_discount'] ?? 0),
                'payment_method' => $validated['payment_method'] ?? 'onsite',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => array_merge($quote, [
                'apartment' => [
                    'id' => $apartment->ID,
                    'name' => $apartment->display_name ?: $apartment->name,
                    'cleaning_fee' => (float) ($apartment->cleaning_fee ?? 0),
                    'airport_pickup_available' => (bool) $apartment->airport_pickup,
                ],
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'apartment_id' => ['required', 'integer'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guest_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'num_cleaning' => ['nullable', 'integer', 'min:0'],
            'airport_pickup' => ['nullable', 'boolean'],
            'promo_code' => ['nullable', 'string', 'max:20'],
            'promo_code_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_method' => ['nullable', Rule::in(['onsite', 'card'])],
        ]);

        try {
            $booking = $this->bookingService->createGuest($validated);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $this->confirmationEmailService->sendGuestConfirmation($booking);

        return response()->json([
            'data' => [
                'id' => $booking->ID,
                'booking_num' => $booking->booking_num,
                'status' => $booking->status,
                'total' => (float) $booking->total,
                'check_in' => $booking->check_in_date->format('Y-m-d'),
                'check_out' => $booking->check_out_date->format('Y-m-d'),
                'guest' => trim($booking->firstname.' '.$booking->lastname),
                'email' => $booking->email,
            ],
            'message' => 'Booking submitted. We will confirm your reservation shortly.',
        ], 201);
    }
}
