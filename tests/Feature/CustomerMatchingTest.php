<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerMatchingTest extends TestCase
{
    use DatabaseTransactions;

    private function createApartmentForHost(User $host): Apartment
    {
        $response = $this->actingAs($host)->postJson('/api/apartments', [
            'building_id' => 476,
            'district_id' => 475,
            'apartment_type' => '2BR',
            'quality_standard' => 'standard',
            'room_number' => 'MatchingTest-'.uniqid(),
            'facilities' => [],
            'status' => 'active',
        ]);
        $response->assertCreated();

        return Apartment::query()->findOrFail($response->json('data.id'));
    }

    public function test_booking_with_email_merges_into_an_existing_no_email_customer_of_the_same_name(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $apartment = $this->createApartmentForHost($host);

        // Host adds a temporary customer with no email first.
        $this->actingAs($host)->postJson('/api/customers', [
            'name' => 'Ji Chang Wook',
        ])->assertCreated();

        // Later, a booking is created for "the same" guest, this time with an email.
        $this->actingAs($host)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => 'Ji Chang Wook',
            'email' => 'jichangwook@mail.com',
            'guests' => 1,
            'daily_price' => 500000,
            'check_in_date' => now()->addYears(2)->format('Y-m-d'),
            'check_out_date' => now()->addYears(2)->addDay()->format('Y-m-d'),
        ])->assertCreated();

        $response = $this->actingAs($host)->getJson('/api/customers');
        $rows = collect($response->json('data'))->where('name', 'Ji Chang Wook');

        $this->assertCount(1, $rows, 'Expected a single Ji Chang Wook customer, not a duplicate.');
        $this->assertSame(1, $rows->first()['bookings_count']);
        $this->assertSame('jichangwook@mail.com', $rows->first()['email']);
    }

    public function test_a_shared_email_does_not_merge_two_differently_named_guests(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $apartment = $this->createApartmentForHost($host);

        $this->actingAs($host)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => 'Khoa Cao Anh',
            'email' => 'shared@example.com',
            'guests' => 1,
            'daily_price' => 500000,
            'check_in_date' => now()->addYears(2)->format('Y-m-d'),
            'check_out_date' => now()->addYears(2)->addDay()->format('Y-m-d'),
        ])->assertCreated();

        // A different guest, typo'd into using the same email.
        $this->actingAs($host)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => 'Leo Messi',
            'email' => 'shared@example.com',
            'guests' => 1,
            'daily_price' => 500000,
            'check_in_date' => now()->addYears(2)->addDays(10)->format('Y-m-d'),
            'check_out_date' => now()->addYears(2)->addDays(11)->format('Y-m-d'),
        ])->assertCreated();

        $response = $this->actingAs($host)->getJson('/api/customers');
        $rows = collect($response->json('data'))->keyBy('name');

        $this->assertTrue($rows->has('Khoa Cao Anh'));
        $this->assertTrue($rows->has('Leo Messi'));
        $this->assertSame(1, $rows['Khoa Cao Anh']['bookings_count']);
        $this->assertSame(1, $rows['Leo Messi']['bookings_count']);
    }
}
