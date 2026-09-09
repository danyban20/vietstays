<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerSegmentsAndFiltersTest extends TestCase
{
    use DatabaseTransactions;

    private function createApartmentForHost(User $host): Apartment
    {
        $response = $this->actingAs($host)->postJson('/api/apartments', [
            'building_id' => 476,
            'district_id' => 475,
            'apartment_type' => '2BR',
            'quality_standard' => 'standard',
            'room_number' => 'SegmentTest-'.uniqid(),
            'facilities' => [],
            'status' => 'active',
        ]);
        $response->assertCreated();

        return Apartment::query()->findOrFail($response->json('data.id'));
    }

    private function createBooking(User $host, Apartment $apartment, string $guestName, string $checkIn, string $checkOut): void
    {
        $this->actingAs($host)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => $guestName,
            'email' => strtolower(str_replace(' ', '.', $guestName)).'@example.com',
            'guests' => 1,
            'daily_price' => 500000,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
        ])->assertCreated();
    }

    public function test_customer_segments_and_tab_counts_are_computed_correctly(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $this->assertNotNull($host);
        $apartment = $this->createApartmentForHost($host);

        $future = now()->addYears(2);

        // VIP: 4 stays
        foreach (range(0, 3) as $i) {
            $this->createBooking(
                $host,
                $apartment,
                'Vip Guest',
                $future->copy()->addDays($i * 20)->format('Y-m-d'),
                $future->copy()->addDays($i * 20 + 2)->format('Y-m-d'),
            );
        }

        // Repeat: 2 stays
        foreach (range(0, 1) as $i) {
            $this->createBooking(
                $host,
                $apartment,
                'Repeat Guest',
                $future->copy()->addDays(200 + $i * 20)->format('Y-m-d'),
                $future->copy()->addDays(200 + $i * 20 + 2)->format('Y-m-d'),
            );
        }

        // New: 1 stay, upcoming, and within the next 3 days -> also needs_action
        $this->createBooking(
            $host,
            $apartment,
            'New Guest',
            now()->addDays(2)->format('Y-m-d'),
            now()->addDays(4)->format('Y-m-d'),
        );

        $response = $this->actingAs($host)->getJson('/api/customers');
        $response->assertOk();

        $rows = collect($response->json('data'))->keyBy('name');

        $this->assertSame('vip', $rows['Vip Guest']['segment']);
        $this->assertSame(4, $rows['Vip Guest']['bookings_count']);

        $this->assertSame('repeat', $rows['Repeat Guest']['segment']);
        $this->assertSame(2, $rows['Repeat Guest']['bookings_count']);

        $this->assertSame('new', $rows['New Guest']['segment']);
        $this->assertSame('upcoming', $rows['New Guest']['status']);
        $this->assertTrue($rows['New Guest']['needs_action']);

        $meta = $response->json('meta');
        $this->assertSame(2, $meta['repeat_count']); // vip guest + repeat guest
        $this->assertGreaterThanOrEqual(1, $meta['tab_counts']['needs_action']);

        // Tab filter: repeat should only include vip + repeat guests
        $repeatTab = $this->actingAs($host)->getJson('/api/customers?tab=repeat');
        $repeatNames = collect($repeatTab->json('data'))->pluck('name');
        $this->assertTrue($repeatNames->contains('Vip Guest'));
        $this->assertTrue($repeatNames->contains('Repeat Guest'));
        $this->assertFalse($repeatNames->contains('New Guest'));
    }

    public function test_sort_by_name_orders_alphabetically(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $apartment = $this->createApartmentForHost($host);

        $this->createBooking($host, $apartment, 'Zed Guest', now()->addYears(2)->format('Y-m-d'), now()->addYears(2)->addDay()->format('Y-m-d'));
        $this->createBooking($host, $apartment, 'Anna Guest', now()->addYears(2)->addDays(10)->format('Y-m-d'), now()->addYears(2)->addDays(11)->format('Y-m-d'));

        $response = $this->actingAs($host)->getJson('/api/customers?sort=name');
        $names = collect($response->json('data'))->pluck('name')->values();

        $zedIndex = $names->search('Zed Guest');
        $annaIndex = $names->search('Anna Guest');
        $this->assertLessThan($zedIndex, $annaIndex);
    }

    public function test_export_returns_an_xlsx_file(): void
    {
        $host = User::query()->where('role', 'host')->first();
        $apartment = $this->createApartmentForHost($host);
        $this->createBooking($host, $apartment, 'Export Guest', now()->addYears(2)->format('Y-m-d'), now()->addYears(2)->addDay()->format('Y-m-d'));

        $response = $this->actingAs($host)->get('/api/customers/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
