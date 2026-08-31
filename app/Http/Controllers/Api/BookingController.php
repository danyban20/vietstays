<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Booking;
use App\Services\BookingCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __construct(
        protected BookingCreationService $bookingService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Booking::query()->with('apartment')->orderByDesc('check_in_date');

        if ($request->user()->isPartner() && ! $request->user()->isAdmin()) {
            $query->whereIn('apartment_id', function ($q) use ($request) {
                $q->select('ID')
                    ->from('vv_apartments')
                    ->where('user_id', $request->user()->legacy_wp_id);
            });
        }

        if ($request->filled('statuses')) {
            $statuses = array_values(array_filter(array_map(
                'trim',
                is_array($request->input('statuses'))
                    ? $request->input('statuses')
                    : explode(',', $request->string('statuses'))
            )));

            if ($statuses !== []) {
                $query->whereIn('status', $statuses);
            }
        } elseif ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('outstanding_only')) {
            $today = now()->startOfDay();
            $query->where(function ($q) use ($today) {
                $q->where('status', 'pending')
                    ->orWhere(function ($q2) use ($today) {
                        $q2->where('status', 'confirmed')
                            ->whereDate('check_out_date', '<=', $today);
                    });
            });
        }

        if ($request->filled('commission')) {
            $commission = $request->string('commission');

            if ($commission === 'host_agent') {
                $query->where('extra_data->discount_type', 'host_agent');
            } elseif ($commission === 'ambassador') {
                $query->where('extra_data->discount_type', 'ambassador');
            } elseif ($commission === 'none') {
                $query->where(function ($q) {
                    $q->whereNull('extra_data')
                        ->orWhereNull('extra_data->discount_type')
                        ->orWhereNotIn('extra_data->discount_type', ['host_agent', 'ambassador']);
                });
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('check_out_date', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('check_in_date', '<=', $request->string('to'));
        }

        if ($request->filled('apartment')) {
            $query->where('apartment_id', $request->integer('apartment'));
        }

        if ($request->filled('district')) {
            $query->where('district_id', $request->integer('district'));
        }

        if ($request->filled('building')) {
            $buildingId = $request->integer('building');
            $query->whereIn('apartment_id', function ($q) use ($buildingId) {
                $q->select('ID')->from('vv_apartments')->where('building_id', $buildingId);
            });
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('booking_num', 'like', "%{$search}%");
            });
        }

        $bookings = $query->limit(200)->get()->map(fn (Booking $b) => $this->transform($b));

        return response()->json(['data' => $bookings]);
    }

    public function show(Request $request, int $booking): JsonResponse
    {
        $model = Booking::query()->with('apartment')->findOrFail($booking);
        $this->authorizeBooking($request, $model);

        return response()->json(['data' => $this->transform($model, true)]);
    }

    public function store(Request $request): JsonResponse
    {
        $type = $request->string('type', 'manual');

        if ($type === 'block') {
            return $this->storeBlock($request);
        }

        if ($type === 'external') {
            return $this->storeExternal($request);
        }

        $validated = $request->validate([
            'apartment_id' => ['required', 'integer'],
            'guest_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'guests' => ['nullable', 'integer', 'min:1'],
            'daily_price' => ['nullable', 'numeric', 'min:0'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'discount_enabled' => ['nullable', 'boolean'],
            'discount_type' => ['nullable', Rule::in(['percentage', 'ambassador', 'host_agent'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_code' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', Rule::in(['confirmed', 'pending', 'cancelled'])],
        ]);

        try {
            $booking = $this->bookingService->createManual($validated, $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => $this->transform($booking, true),
            'message' => 'Booking created.',
        ], 201);
    }

    public function update(Request $request, int $booking): JsonResponse
    {
        $model = Booking::query()->with('apartment')->findOrFail($booking);
        $this->authorizeBooking($request, $model);

        $validated = $request->validate([
            'guest_name' => ['sometimes', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'guests' => ['nullable', 'integer', 'min:1'],
            'daily_price' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'check_in_date' => ['sometimes', 'date'],
            'check_out_date' => ['sometimes', 'date', 'after:check_in_date'],
            'apartment_id' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::in(['confirmed', 'pending', 'cancelled'])],
            'note' => ['nullable', 'string', 'max:2000'],
            'notify_guest' => ['nullable', Rule::in(['email', 'sms', 'none'])],
        ]);

        try {
            $updated = $this->bookingService->update($model, $validated);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $notify = $validated['notify_guest'] ?? 'none';

        return response()->json([
            'data' => $this->transform($updated, true),
            'message' => $notify === 'none'
                ? 'Booking updated.'
                : 'Booking updated. Guest notification queued.',
            'notify_guest' => $notify,
        ]);
    }

    public function move(Request $request, int $booking): JsonResponse
    {
        $model = Booking::query()->with('apartment')->findOrFail($booking);
        $this->authorizeBooking($request, $model);

        $validated = $request->validate([
            'apartment_id' => ['required', 'integer'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'notify_guest' => ['nullable', Rule::in(['email', 'sms', 'none'])],
        ]);

        $targetApartment = Apartment::query()->findOrFail($validated['apartment_id']);
        if ($request->user()->isPartner() && ! $request->user()->isAdmin()) {
            if ((int) $targetApartment->user_id !== (int) $request->user()->legacy_wp_id) {
                abort(403, 'You cannot move a booking to this apartment.');
            }
        }

        try {
            $updated = $this->bookingService->update($model, [
                'apartment_id' => $validated['apartment_id'],
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $notify = $validated['notify_guest'] ?? 'none';

        return response()->json([
            'data' => $this->transform($updated, true),
            'message' => $notify === 'none'
                ? 'Booking moved.'
                : 'Booking moved. Guest notification queued.',
            'notify_guest' => $notify,
        ]);
    }

    protected function storeBlock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'apartment_id' => ['required', 'integer'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $period = $this->bookingService->createBlock($validated, $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'id' => $period->ID,
                'type' => 'block',
                'apartment_id' => $period->apartment_id,
                'start_date' => $period->start_date->format('Y-m-d'),
                'end_date' => $period->end_date->format('Y-m-d'),
                'note' => $period->note,
            ],
            'message' => 'Dates blocked.',
        ], 201);
    }

    protected function storeExternal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'apartment_id' => ['required', 'integer'],
            'external_platform' => ['nullable', 'string', 'max:32'],
            'reference' => ['nullable', 'string', 'max:128'],
            'guest_name' => ['nullable', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:255'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'note' => ['nullable', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $period = $this->bookingService->createExternal($validated, $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'id' => $period->ID,
                'type' => 'external',
                'apartment_id' => $period->apartment_id,
                'start_date' => $period->start_date->format('Y-m-d'),
                'end_date' => $period->end_date->format('Y-m-d'),
                'guest_name' => $period->guest_name,
                'external_platform' => $period->external_platform,
                'reference' => $period->external_booking_ref,
            ],
            'message' => 'External booking registered.',
        ], 201);
    }

    protected function authorizeBooking(Request $request, Booking $booking): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if (! $user->isPartner()) {
            abort(403);
        }

        $owned = Apartment::query()
            ->where('ID', $booking->apartment_id)
            ->where('user_id', $user->legacy_wp_id)
            ->exists();

        if (! $owned) {
            abort(403);
        }
    }

    protected function transform(Booking $booking, bool $detailed = false): array
    {
        $nights = max(1, $booking->check_in_date->diffInDays($booking->check_out_date));
        $extra = is_array($booking->extra_data) ? $booking->extra_data : [];
        $apartment = $booking->apartment;
        $building = $apartment?->building_id
            ? \App\Models\Building::query()->find($apartment->building_id)
            : null;
        $district = $apartment?->district
            ? \App\Models\District::query()->find($apartment->district)
            : null;

        $channel = strtolower((string) ($extra['source'] ?? 'vietstays'));
        $channelLabel = match (true) {
            str_contains($channel, 'airbnb') => 'External (Airbnb)',
            in_array($channel, ['booking.com', 'trip.com', 'external'], true) => 'External booking',
            default => $booking->promo_code
                ? 'Vietstays · '.$booking->promo_code
                : 'Vietstays direct booking',
        };

        $data = [
            'id' => $booking->ID,
            'booking_num' => $booking->booking_num,
            'guest' => trim($booking->firstname.' '.$booking->lastname),
            'email' => $booking->email,
            'phone' => $extra['phone'] ?? '',
            'apartment' => $apartment?->display_name ?: $apartment?->name,
            'apartment_id' => $booking->apartment_id,
            'check_in' => $booking->check_in_date->format('Y-m-d'),
            'check_out' => $booking->check_out_date->format('Y-m-d'),
            'from' => $booking->check_in_date->format('Y-m-d'),
            'to' => $booking->check_out_date->format('Y-m-d'),
            'nights' => $nights,
            'total' => (float) $booking->total,
            'amount' => (float) $booking->total,
            'status' => $booking->status,
            'channel' => $channel,
            'channel_label' => $channelLabel,
            'channel_type' => $this->channelType($channel),
            'guests' => (int) $booking->adults + (int) $booking->children,
            'discount_type' => $extra['discount_type'] ?? null,
        ];

        if ($detailed) {
            $dailyRate = (float) $booking->price;
            $cleaningFee = (float) ($apartment?->cleaning_fee ?? 0);
            $extraCleaning = (float) ($apartment?->extra_cleaning_fee ?? 0);
            $roomTotal = $dailyRate * $nights;
            $discount = (float) $booking->campaign_discount;
            $gmv = max(0, $roomTotal + $cleaningFee - $discount);
            $platformFee = round($gmv * 0.05);
            $cashPoints = round($gmv * 0.03);
            $hostNet = $gmv - $platformFee - $cashPoints;
            $discountType = $extra['discount_type'] ?? null;

            $data += [
                'adults' => (int) $booking->adults,
                'children' => (int) $booking->children,
                'promo_code' => $booking->promo_code,
                'price' => $dailyRate,
                'daily_rate' => $dailyRate,
                'cleaning_fee' => $cleaningFee,
                'extra_cleaning_fee' => $extraCleaning,
                'campaign_discount' => $discount,
                'room_total' => $roomTotal,
                'gmv' => $gmv,
                'platform_fee' => $platformFee,
                'cash_points' => $cashPoints,
                'host_net' => $hostNet,
                'vietstays_net' => $platformFee - $cashPoints,
                'discount_type' => $discountType,
                'commission_label' => $discountType === 'host_agent'
                    ? 'Host agent commission'
                    : ($discountType === 'ambassador' ? 'Ambassador code' : 'Cash points (3%)'),
                'created_at' => $booking->dateadded?->format('Y-m-d'),
                'created_at_time' => $booking->dateadded?->format('H:i'),
                'display_id' => 'BK-'.$booking->ID,
                'note' => $extra['note'] ?? $extra['internal_note'] ?? '',
                'reference' => $booking->booking_num,
                'reject_deadline' => $booking->status === 'pending'
                    ? ($extra['reject_deadline'] ?? $booking->dateadded?->copy()->addHours(24)?->toIso8601String())
                    : null,
                'apartment_detail' => [
                    'name' => $apartment?->display_name ?: $apartment?->name,
                    'district' => $district?->name,
                    'building' => $building?->name,
                    'image' => $this->firstApartmentImage($apartment),
                    'check_in_time' => $apartment?->check_in_time1,
                    'check_out_time' => $apartment?->check_out_time,
                    'type' => $apartment?->apartment_type,
                    'num_bathrooms' => (int) ($apartment?->num_bathrooms ?: 1),
                ],
                'access' => [
                    'door_code' => $extra['door_code'] ?? '',
                    'wifi_network' => $extra['wifi_network'] ?? $extra['wifi_name'] ?? '',
                    'wifi_password' => $extra['wifi_password'] ?? '',
                ],
                'next_task' => $this->nextTaskLabel($booking),
            ];
        }

        return $data;
    }

    protected function firstApartmentImage(?Apartment $apartment): ?string
    {
        if (! $apartment || ! is_array($apartment->images)) {
            return null;
        }

        foreach ($apartment->images as $image) {
            if (! is_array($image)) {
                continue;
            }

            $src = $image['thumb'] ?? $image['url'] ?? '';

            if (filled($src)) {
                return $src;
            }
        }

        return null;
    }

    protected function nextTaskLabel(Booking $booking): ?string
    {
        $today = now()->startOfDay();
        $checkIn = $booking->check_in_date->copy()->startOfDay();
        $checkOut = $booking->check_out_date->copy()->startOfDay();

        if ($booking->status === 'cancelled') {
            return null;
        }

        if ($today->equalTo($checkIn)) {
            return 'Guest check-in today';
        }

        if ($today->equalTo($checkIn->copy()->subDay())) {
            return 'Prepare for check-in tomorrow';
        }

        if ($today->gt($checkIn) && $today->lt($checkOut)) {
            return 'Guest in stay';
        }

        if ($today->equalTo($checkOut->copy()->subDay())) {
            return 'Prepare for check-out tomorrow';
        }

        if ($today->equalTo($checkOut)) {
            return 'Guest check-out today · schedule cleaning';
        }

        if ($today->lt($checkIn)) {
            $days = (int) $today->diffInDays($checkIn);

            return $days === 1
                ? 'Check-in in 1 day'
                : "Check-in in {$days} days";
        }

        return null;
    }

    protected function channelType(string $channel): string
    {
        if (str_contains($channel, 'airbnb')) {
            return 'airbnb';
        }

        if (in_array($channel, ['booking.com', 'trip.com', 'external'], true)) {
            return 'external';
        }

        return 'vietstays';
    }
}
