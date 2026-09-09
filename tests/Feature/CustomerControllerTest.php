<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function createApartmentForHost(User $host): Apartment
    {
        $response = $this->actingAs($host)->postJson('/api/apartments', [
            'building_id' => 476,
            'district_id' => 475,
            'apartment_type' => '2BR',
            'quality_standard' => 'standard',
            'room_number' => 'CustomerTest-'.uniqid(),
            'facilities' => [],
            'price_daily' => 500000,
            'status' => 'active',
        ]);

        $response->assertCreated();

        return Apartment::query()->findOrFail($response->json('data.id'));
    }

    public function test_creating_a_booking_creates_a_linked_customer(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $this->assertNotNull($host);

        $apartment = $this->createApartmentForHost($host);

        $this->actingAs($host)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => 'Jane Customer',
            'email' => 'jane.customer@example.com',
            'guests' => 1,
            'daily_price' => 500000,
            'check_in_date' => now()->addYears(2)->format('Y-m-d'),
            'check_out_date' => now()->addYears(2)->addDay()->format('Y-m-d'),
        ])->assertCreated();

        $response = $this->actingAs($host)->getJson('/api/customers');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('Jane Customer', $names);
    }

    public function test_repeat_booking_from_same_email_reuses_the_same_customer(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $apartment = $this->createApartmentForHost($host);

        $payload = [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => 'Repeat Guest',
            'email' => 'repeat.guest@example.com',
            'guests' => 1,
            'daily_price' => 500000,
        ];

        $this->actingAs($host)->postJson('/api/bookings', $payload + [
            'check_in_date' => now()->addYears(2)->format('Y-m-d'),
            'check_out_date' => now()->addYears(2)->addDay()->format('Y-m-d'),
        ])->assertCreated();

        $this->actingAs($host)->postJson('/api/bookings', $payload + [
            'check_in_date' => now()->addYears(2)->addDays(10)->format('Y-m-d'),
            'check_out_date' => now()->addYears(2)->addDays(11)->format('Y-m-d'),
        ])->assertCreated();

        $response = $this->actingAs($host)->getJson('/api/customers');
        $response->assertOk();

        $customer = collect($response->json('data'))->firstWhere('name', 'Repeat Guest');
        $this->assertNotNull($customer);
        $this->assertSame(2, $customer['bookings_count']);
    }

    public function test_customer_list_is_scoped_to_owner(): void
    {
        $hostA = User::query()->where('role', 'host')->first();
        $hostB = User::query()->where('role', 'partner')->first();
        $this->assertNotNull($hostA);
        $this->assertNotNull($hostB);

        $apartmentA = $this->createApartmentForHost($hostA);

        $this->actingAs($hostA)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartmentA->ID,
            'guest_name' => 'Only Host A Sees Me',
            'guests' => 1,
            'daily_price' => 500000,
            'check_in_date' => now()->addYears(2)->format('Y-m-d'),
            'check_out_date' => now()->addYears(2)->addDay()->format('Y-m-d'),
        ])->assertCreated();

        $response = $this->actingAs($hostB)->getJson('/api/customers');
        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name');
        $this->assertNotContains('Only Host A Sees Me', $names);
    }

    public function test_can_manually_add_a_customer_without_a_booking(): void
    {
        $host = User::query()->where('role', 'host')->first();

        $response = $this->actingAs($host)->postJson('/api/customers', [
            'name' => 'Manually Added',
            'email' => 'manual@example.com',
            'phone' => '0901234567',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Manually Added');
        $response->assertJsonPath('data.bookings_count', 0);
    }
}
