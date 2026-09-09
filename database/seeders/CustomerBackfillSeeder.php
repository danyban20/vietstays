<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Booking;
use App\Services\CustomerService;
use Illuminate\Database\Seeder;

/**
 * Links pre-existing bookings (imported from legacy WordPress data, or created
 * before the customers table existed) to a Customer record. Safe to re-run —
 * bookings that already have a customer_id are skipped.
 */
class CustomerBackfillSeeder extends Seeder
{
    public function run(CustomerService $customerService): void
    {
        $apartmentOwners = Apartment::query()->pluck('user_id', 'ID');

        $linked = 0;

        Booking::query()
            ->whereNull('customer_id')
            ->orderBy('ID')
            ->chunkById(200, function ($bookings) use ($apartmentOwners, $customerService, &$linked) {
                foreach ($bookings as $booking) {
                    $ownerUserId = (int) ($apartmentOwners[$booking->apartment_id] ?? 0);
                    $name = trim($booking->firstname.' '.$booking->lastname) ?: 'Guest';

                    $customer = $customerService->findOrCreateForBooking(
                        $ownerUserId,
                        $name,
                        $booking->email ?: null,
                        null,
                    );

                    $booking->update(['customer_id' => $customer->id]);
                    $linked++;
                }
            }, 'ID');

        $this->command?->info("Linked {$linked} existing bookings to customer records.");
    }
}
