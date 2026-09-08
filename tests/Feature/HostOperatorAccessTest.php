<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HostOperatorAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function createApartmentForHost(User $host): Apartment
    {
        $response = $this->actingAs($host)->postJson('/api/apartments', [
            'building_id' => 476,
            'district_id' => 475,
            'apartment_type' => '2BR',
            'quality_standard' => 'standard',
            'room_number' => 'HostAccessTest-'.uniqid(),
            'facilities' => [],
            'price_daily' => 500000,
            'status' => 'active',
        ]);

        $response->assertCreated();

        return Apartment::query()->findOrFail($response->json('data.id'));
    }

    public function test_host_role_can_create_a_manual_booking_for_their_own_apartment(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $this->assertNotNull($host, 'Fixture needs at least one role=host user.');

        $apartment = $this->createApartmentForHost($host);

        $response = $this->actingAs($host)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => 'Operator Access Test',
            'guests' => 1,
            'daily_price' => 500000,
            'check_in_date' => now()->addYears(2)->format('Y-m-d'),
            'check_out_date' => now()->addYears(2)->addDay()->format('Y-m-d'),
        ]);

        $response->assertCreated();
    }

    public function test_host_role_apartment_list_is_scoped_to_their_own(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $this->assertNotNull($host);

        $ownApartment = $this->createApartmentForHost($host);
        $totalBefore = Apartment::query()->count();
        $this->assertGreaterThan(1, $totalBefore, 'Fixture needs apartments owned by other users too.');

        $response = $this->actingAs($host)->getJson('/api/apartments');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($ownApartment->ID));
        $this->assertCount(1, $ids, 'Host should only see their own apartment(s), not everyone\'s.');
    }

    public function test_host_role_booking_list_is_scoped_to_their_own_apartments(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $this->assertNotNull($host);

        $apartment = $this->createApartmentForHost($host);

        $this->actingAs($host)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => 'Operator Booking Scope Test',
            'guests' => 1,
            'daily_price' => 500000,
            'check_in_date' => now()->addYears(2)->addDays(10)->format('Y-m-d'),
            'check_out_date' => now()->addYears(2)->addDays(11)->format('Y-m-d'),
        ])->assertCreated();

        $response = $this->actingAs($host)->getJson('/api/bookings');

        $response->assertOk();
        $returnedApartmentIds = collect($response->json('data'))->pluck('apartment_id')->unique();

        foreach ($returnedApartmentIds as $apartmentId) {
            $this->assertSame(
                $apartment->ID,
                $apartmentId,
                "Booking list leaked apartment {$apartmentId}, which does not belong to this host.",
            );
        }
    }
}
