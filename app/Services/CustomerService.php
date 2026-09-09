<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    /**
     * Find an existing customer for this host by exact (case-insensitive)
     * name, or create a new one. Used when a booking is created so every
     * booking ends up linked to a customer record.
     *
     * Matches on name, not email: two different guests can share an email
     * (family booking, a typo) without being silently merged into one
     * customer just because the email matched. A returning guest is far more
     * reliably identified by typing the same name again than by email, which
     * often isn't collected at all for a manual/offline booking.
     */
    public function findOrCreateForBooking(int $ownerUserId, string $name, ?string $email, ?string $phone): Customer
    {
        $name = trim($name) ?: 'Guest';
        $email = $email ? trim($email) : null;

        $customer = Customer::query()
            ->where('user_id', $ownerUserId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($customer) {
            $updates = array_filter([
                'email' => ! $customer->email && $email ? $email : null,
                'phone' => ! $customer->phone && $phone ? $phone : null,
            ]);

            if ($updates) {
                $customer->update($updates);
            }

            return $customer;
        }

        return Customer::query()->create([
            'user_id' => $ownerUserId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ]);
    }
}
