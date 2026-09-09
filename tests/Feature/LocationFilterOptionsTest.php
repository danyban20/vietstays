<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LocationFilterOptionsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * The Add Booking modal auto-fills the nightly rate from this list once an
     * apartment is picked — without price_daily here, that auto-fill silently
     * never happens and the "Price Suggestion" box becomes the only way to
     * ever see a price.
     */
    public function test_apartments_list_includes_price_daily(): void
    {
        $user = User::query()->where('legacy_wp_id', 3)->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->postJson('/api/apartments', [
            'building_id' => 476,
            'district_id' => 475,
            'apartment_type' => '4BR',
            'quality_standard' => 'standard',
            'room_number' => 'FilterOptionsTest-'.uniqid(),
            'facilities' => [],
            'status' => 'active',
        ]);
        $response->assertCreated();

        $apartmentId = $response->json('data.id');
        $expectedPrice = (float) Apartment::query()->findOrFail($apartmentId)->price_daily;
        $this->assertGreaterThan(0, $expectedPrice);

        $filters = $this->actingAs($user)->getJson('/api/locations/filters');
        $filters->assertOk();

        $listed = collect($filters->json('data.apartments'))->firstWhere('id', $apartmentId);
        $this->assertNotNull($listed);
        $this->assertEquals($expectedPrice, $listed['price_daily']);
    }
}
