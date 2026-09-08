<?php

namespace App\Services;

use App\Models\ApartmentAvailabilityPeriod;
use App\Models\Booking;
use Carbon\Carbon;

class BookingAvailabilityService
{
    public function isAvailable(
        int $apartmentId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?int $excludeBookingId = null,
        ?int $excludePeriodId = null,
    ): bool {
        $checkIn = $checkIn->copy()->startOfDay();
        $checkOut = $checkOut->copy()->startOfDay();

        if ($checkOut->lte($checkIn)) {
            return false;
        }

        $bookingConflict = Booking::query()
            ->where('apartment_id', $apartmentId)
            ->where('status', '!=', 'cancelled')
            ->when($excludeBookingId, fn ($q) => $q->where('ID', '!=', $excludeBookingId))
            ->whereDate('check_in_date', '<', $checkOut)
            ->whereDate('check_out_date', '>', $checkIn)
            ->exists();

        if ($bookingConflict) {
            return false;
        }

        return ! ApartmentAvailabilityPeriod::query()
            ->where('apartment_id', $apartmentId)
            ->when($excludePeriodId, fn ($q) => $q->where('ID', '!=', $excludePeriodId))
            ->whereDate('start_date', '<', $checkOut)
            ->whereDate('end_date', '>=', $checkIn)
            ->exists();
    }

    public function assertAvailable(
        int $apartmentId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?int $excludeBookingId = null,
        ?int $excludePeriodId = null,
    ): void {
        if (! $this->isAvailable($apartmentId, $checkIn, $checkOut, $excludeBookingId, $excludePeriodId)) {
            throw new \InvalidArgumentException('Selected dates are unavailable.');
        }
    }
}
