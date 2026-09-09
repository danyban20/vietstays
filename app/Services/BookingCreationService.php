<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\ApartmentAvailabilityPeriod;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingCreationService
{
    public function __construct(
        protected BookingPricingService $pricingService,
        protected BookingAvailabilityService $availabilityService,
        protected CustomerService $customerService,
    ) {}

    public function createGuest(array $payload): Booking
    {
        $apartment = Apartment::query()
            ->where('status', 'active')
            ->findOrFail($payload['apartment_id']);

        $checkIn = Carbon::parse($payload['check_in_date'])->startOfDay();
        $checkOut = Carbon::parse($payload['check_out_date'])->startOfDay();

        $adults = max(1, (int) ($payload['adults'] ?? 1));
        $children = max(0, (int) ($payload['children'] ?? 0));
        $totalGuests = $adults + $children;

        if ($totalGuests > (int) ($apartment->max_guests ?? 0)) {
            throw new \InvalidArgumentException(
                'This apartment allows a maximum of '.(int) $apartment->max_guests.' guest(s).'
            );
        }

        $this->availabilityService->assertAvailable($apartment->ID, $checkIn, $checkOut);

        $quote = $this->pricingService->quote($apartment, $checkIn, $checkOut, [
            'num_cleaning' => (int) ($payload['num_cleaning'] ?? 0),
            'airport_pickup' => ! empty($payload['airport_pickup']),
            'promo_code' => $payload['promo_code'] ?? '',
            'promo_code_discount_percent' => (float) ($payload['promo_code_discount'] ?? 0),
            'payment_method' => $payload['payment_method'] ?? 'onsite',
        ]);

        $dates = [];
        $cursor = $checkIn->copy();
        while ($cursor->lt($checkOut)) {
            $dates[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $nights = max(1, count($dates));
        $guestName = trim($payload['guest_name'] ?? '');
        [$firstname, $lastname] = $this->splitName($guestName);
        $now = now();

        $customerId = $this->customerService->findOrCreateForBooking(
            (int) $apartment->user_id,
            $guestName,
            $payload['email'] ?? null,
            $payload['phone'] ?? null,
        )->id;

        $discountTotal = (float) $quote['campaign_discount']
            + (float) $quote['basic_discount_amount']
            + (float) $quote['promo_discount_amount'];

        return DB::transaction(function () use (
            $payload, $apartment, $checkIn, $checkOut, $dates, $nights,
            $firstname, $lastname, $now, $quote, $discountTotal, $adults, $children, $customerId
        ) {
            $extraData = array_filter([
                'phone' => $payload['phone'] ?? null,
                'source' => 'guest_checkout',
                'payment_method' => $quote['payment_method'] ?? 'onsite',
                'num_cleaning' => (int) ($quote['num_cleaning'] ?? 0),
                'airport_pickup' => ! empty($quote['airport_pickup']),
                'airport_pickup_cost' => (float) ($quote['airport_pickup_cost'] ?? 0),
            ]);

            $dailyPrice = $nights > 0 ? round(((float) $quote['room_total']) / $nights, 2) : 0;

            $booking = Booking::query()->create([
                'ID' => $this->nextBookingId(),
                'booking_num' => '',
                'user_id' => 0,
                'customer_id' => $customerId,
                'apartment_id' => $apartment->ID,
                'district_id' => (int) $apartment->district,
                'email' => $payload['email'] ?? '',
                'firstname' => $firstname,
                'lastname' => $lastname,
                'dates' => $dates,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'extra_data' => $extraData ?: null,
                'price' => $dailyPrice,
                'basic_discount' => (float) ($quote['basic_discount_percent'] ?? 0),
                'campaign_discount' => $discountTotal,
                'campaign_discount_desc' => $quote['campaign_discount_desc'] ?? [],
                'ambassador_id' => 0,
                'ambassador_commission' => 0,
                'promo_code' => $quote['promo_code'] ?? '',
                'promo_code_discount' => (float) ($quote['promo_code_discount_percent'] ?? 0),
                'booking_fee' => (float) ($quote['booking_fee_percent'] ?? 5),
                'total' => (float) $quote['total'],
                'adults' => $adults,
                'children' => $children,
                'child_ages' => [],
                'status' => 'pending',
                'dateadded' => $now,
                'datemodified' => $now,
            ]);

            $booking->update([
                'booking_num' => $this->generateBookingNum($booking),
            ]);

            foreach ($quote['rate_lines'] as $line) {
                DB::table('vv_booking_items')->insert([
                    'item_id' => $this->nextBookingItemId(),
                    'booking_id' => $booking->ID,
                    'code' => 'booking-date',
                    'item_type' => 'booking-date',
                    'name' => Carbon::parse($line['start'])->format('m/d/Y').'-'.Carbon::parse($line['end'])->format('m/d/Y'),
                    'qty' => (int) $line['nights'],
                    'price' => (float) $line['price'],
                    'dateadded' => $now,
                    'datemodified' => $now,
                ]);
            }

            if ((float) ($quote['cleaning_fee'] ?? 0) > 0) {
                DB::table('vv_booking_items')->insert([
                    'item_id' => $this->nextBookingItemId(),
                    'booking_id' => $booking->ID,
                    'code' => 'cleaning_fee',
                    'item_type' => 'fee',
                    'name' => 'Cleaning fee',
                    'qty' => 1,
                    'price' => (float) $quote['cleaning_fee'],
                    'dateadded' => $now,
                    'datemodified' => $now,
                ]);
            }

            return $booking->fresh(['apartment']);
        });
    }

    public function createManual(array $payload, User $user): Booking
    {
        $apartment = Apartment::query()->findOrFail($payload['apartment_id']);
        $this->assertApartmentAccess($apartment, $user);

        $checkIn = Carbon::parse($payload['check_in_date'])->startOfDay();
        $checkOut = Carbon::parse($payload['check_out_date'])->startOfDay();

        if ($checkOut->lte($checkIn)) {
            throw new \InvalidArgumentException('Check-out must be after check-in.');
        }

        $this->availabilityService->assertAvailable($apartment->ID, $checkIn, $checkOut);

        $dates = [];
        $cursor = $checkIn->copy();
        while ($cursor->lt($checkOut)) {
            $dates[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $nights = max(1, count($dates));
        $dailyPrice = (float) ($payload['daily_price'] ?? $apartment->price_daily);
        if ($dailyPrice <= 0) {
            $dailyPrice = (float) $apartment->price_daily;
        }

        $cleaningFee = (float) ($payload['cleaning_fee'] ?? $apartment->cleaning_fee ?? 0);
        $roomTotal = $dailyPrice * $nights;
        $discount = $this->calculateDiscount($payload, $roomTotal + $cleaningFee);
        $total = max(0, $roomTotal + $cleaningFee - $discount);

        $guestName = trim($payload['guest_name'] ?? '');
        [$firstname, $lastname] = $this->splitName($guestName);

        $now = now();

        $customerId = $this->customerService->findOrCreateForBooking(
            (int) $apartment->user_id,
            $guestName,
            $payload['email'] ?? null,
            $payload['phone'] ?? null,
        )->id;

        return DB::transaction(function () use (
            $payload, $apartment, $checkIn, $checkOut, $dates, $nights,
            $dailyPrice, $total, $firstname, $lastname, $now, $discount, $customerId
        ) {
            $extraData = array_filter([
                'phone' => $payload['phone'] ?? null,
                'country_code' => $payload['country_code'] ?? null,
                'source' => 'manual',
                'discount_type' => $payload['discount_type'] ?? null,
                'discount_value' => $payload['discount_value'] ?? null,
                'discount_code' => $payload['discount_code'] ?? null,
            ]);

            $booking = Booking::query()->create([
                'ID' => $this->nextBookingId(),
                'booking_num' => '',
                'user_id' => 0,
                'customer_id' => $customerId,
                'apartment_id' => $apartment->ID,
                'district_id' => (int) $apartment->district,
                'email' => $payload['email'] ?? '',
                'firstname' => $firstname,
                'lastname' => $lastname,
                'dates' => $dates,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'extra_data' => $extraData ?: null,
                'price' => $dailyPrice,
                'basic_discount' => 0,
                'campaign_discount' => $discount,
                'campaign_discount_desc' => [],
                'ambassador_id' => 0,
                'ambassador_commission' => 0,
                'promo_code' => $payload['discount_code'] ?? '',
                'promo_code_discount' => ($payload['discount_type'] ?? '') === 'ambassador' ? $discount : 0,
                'booking_fee' => 5,
                'total' => $total,
                'adults' => max(1, (int) ($payload['adults'] ?? $payload['guests'] ?? 1)),
                'children' => (int) ($payload['children'] ?? 0),
                'child_ages' => [],
                'status' => $payload['status'] ?? 'confirmed',
                'dateadded' => $now,
                'datemodified' => $now,
            ]);

            $booking->update([
                'booking_num' => $this->generateBookingNum($booking),
            ]);

            DB::table('vv_booking_items')->insert([
                'item_id' => $this->nextBookingItemId(),
                'booking_id' => $booking->ID,
                'code' => 'booking-date',
                'item_type' => 'booking-date',
                'name' => 'Accommodation',
                'qty' => $nights,
                'price' => $dailyPrice,
                'dateadded' => $now,
                'datemodified' => $now,
            ]);

            return $booking->fresh(['apartment']);
        });
    }

    public function createBlock(array $payload, User $user): ApartmentAvailabilityPeriod
    {
        $apartment = Apartment::query()->findOrFail($payload['apartment_id']);
        $this->assertApartmentAccess($apartment, $user);

        $start = Carbon::parse($payload['start_date'])->startOfDay();
        $end = Carbon::parse($payload['end_date'])->startOfDay();

        if ($end->lt($start)) {
            throw new \InvalidArgumentException('End date must be on or after start date.');
        }

        $this->availabilityService->assertAvailable($apartment->ID, $start, $end->copy()->addDay());

        $now = now();

        return ApartmentAvailabilityPeriod::query()->create([
            'ID' => $this->nextAvailabilityPeriodId(),
            'apartment_id' => $apartment->ID,
            'period_type' => 'manual_block',
            'start_date' => $start,
            'end_date' => $end,
            'note' => $payload['note'] ?? '',
            'dateadded' => $now,
            'datemodified' => $now,
        ]);
    }

    public function createExternal(array $payload, User $user): ApartmentAvailabilityPeriod
    {
        $apartment = Apartment::query()->findOrFail($payload['apartment_id']);
        $this->assertApartmentAccess($apartment, $user);

        $start = Carbon::parse($payload['check_in_date'])->startOfDay();
        $end = Carbon::parse($payload['check_out_date'])->startOfDay();

        if ($end->lte($start)) {
            throw new \InvalidArgumentException('Check-out must be after check-in.');
        }

        $this->availabilityService->assertAvailable($apartment->ID, $start, $end);

        $now = now();
        $guestName = trim($payload['guest_name'] ?? '');

        return ApartmentAvailabilityPeriod::query()->create([
            'ID' => $this->nextAvailabilityPeriodId(),
            'apartment_id' => $apartment->ID,
            'period_type' => 'external',
            'start_date' => $start,
            'end_date' => $end->copy()->subDay(),
            'note' => $payload['note'] ?? '',
            'guest_name' => $guestName,
            'guest_email' => $payload['email'] ?? '',
            'source' => $payload['source'] ?? 'external',
            'external_platform' => $payload['external_platform'] ?? '',
            'external_booking_ref' => $payload['reference'] ?? '',
            'dateadded' => $now,
            'datemodified' => $now,
        ]);
    }

    public function update(Booking $booking, array $payload): Booking
    {
        $data = [];
        $now = now();

        if (isset($payload['guest_name'])) {
            [$firstname, $lastname] = $this->splitName($payload['guest_name']);
            $data['firstname'] = $firstname;
            $data['lastname'] = $lastname;
        }

        foreach (['email', 'status'] as $field) {
            if (array_key_exists($field, $payload)) {
                $data[$field] = $payload[$field];
            }
        }

        if (isset($payload['adults']) || isset($payload['guests'])) {
            $data['adults'] = max(1, (int) ($payload['adults'] ?? $payload['guests']));
        }

        if (isset($payload['check_in_date'], $payload['check_out_date'])) {
            $checkIn = Carbon::parse($payload['check_in_date'])->startOfDay();
            $checkOut = Carbon::parse($payload['check_out_date'])->startOfDay();

            if ($checkOut->lte($checkIn)) {
                throw new \InvalidArgumentException('Check-out must be after check-in.');
            }

            $targetApartmentId = isset($payload['apartment_id'])
                ? (int) $payload['apartment_id']
                : $booking->apartment_id;

            $this->availabilityService->assertAvailable(
                $targetApartmentId,
                $checkIn,
                $checkOut,
                excludeBookingId: $booking->ID,
            );

            $dates = [];
            $cursor = $checkIn->copy();
            while ($cursor->lt($checkOut)) {
                $dates[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }

            $data['check_in_date'] = $checkIn;
            $data['check_out_date'] = $checkOut;
            $data['dates'] = $dates;
        }

        if (isset($payload['apartment_id'])) {
            $apartment = Apartment::query()->findOrFail((int) $payload['apartment_id']);
            $data['apartment_id'] = $apartment->ID;
            $data['district_id'] = (int) $apartment->district;
        }

        if (isset($payload['daily_price'])) {
            $data['price'] = (float) $payload['daily_price'];
        }

        if (isset($payload['total'])) {
            $data['total'] = (float) $payload['total'];
        }

        $extra = is_array($booking->extra_data) ? $booking->extra_data : [];
        if (isset($payload['phone'])) {
            $extra['phone'] = $payload['phone'];
            $data['extra_data'] = $extra;
        }

        if (array_key_exists('note', $payload)) {
            $extra['note'] = $payload['note'] ?? '';
            $data['extra_data'] = $extra;
        }

        if (isset($data['check_in_date'], $data['check_out_date']) || isset($data['price'])) {
            $checkIn = $data['check_in_date'] ?? $booking->check_in_date->copy()->startOfDay();
            $checkOut = $data['check_out_date'] ?? $booking->check_out_date->copy()->startOfDay();
            $nights = max(1, $checkIn->diffInDays($checkOut));
            $dailyRate = (float) ($data['price'] ?? $booking->price);
            $apartment = $booking->apartment;
            $cleaningFee = (float) ($apartment?->cleaning_fee ?? 0);
            $discount = (float) $booking->campaign_discount;
            $roomTotal = $dailyRate * $nights;
            $data['total'] = max(0, $roomTotal + $cleaningFee - $discount);
        }

        $data['datemodified'] = $now;
        $booking->update($data);

        return $booking->fresh(['apartment']);
    }

    protected function assertApartmentAccess(Apartment $apartment, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isOperator() && (int) $apartment->user_id === (int) $user->legacy_wp_id) {
            return;
        }

        abort(403, 'You do not have permission for this apartment.');
    }

    protected function splitName(string $fullName): array
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return ['Guest', ''];
        }

        $parts = preg_split('/\s+/', $fullName, 2);

        return [$parts[0], $parts[1] ?? ''];
    }

    protected function calculateDiscount(array $payload, float $subtotal): float
    {
        if (empty($payload['discount_enabled'])) {
            return 0;
        }

        $type = $payload['discount_type'] ?? 'percentage';
        $value = (float) ($payload['discount_value'] ?? 0);

        if ($type === 'percentage' && $value > 0) {
            return round($subtotal * ($value / 100));
        }

        if (in_array($type, ['ambassador', 'host_agent'], true) && $value > 0) {
            return min($value, $subtotal);
        }

        return 0;
    }

    protected function generateBookingNum(Booking $booking): string
    {
        $date = $booking->dateadded ?? now();

        return $date->format('Ym').sprintf('%05d', $booking->ID);
    }

    protected function nextBookingId(): int
    {
        $max = (int) Booking::query()->lockForUpdate()->max('ID');

        return $max + 1;
    }

    protected function nextBookingItemId(): int
    {
        $max = (int) DB::table('vv_booking_items')->lockForUpdate()->max('item_id');

        return $max + 1;
    }

    protected function nextAvailabilityPeriodId(): int
    {
        $max = (int) ApartmentAvailabilityPeriod::query()->lockForUpdate()->max('ID');

        return $max + 1;
    }
}
