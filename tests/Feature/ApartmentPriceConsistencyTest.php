<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Building;
use App\Models\User;
use App\Services\PriceMatrixService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The apartment's own price_daily (set on creation) and the "suggested price"
 * shown on the apartment detail page must come from the same formula
 * (PriceMatrixService) — they used to come from two different, conflicting
 * formulas, which is why they visibly disagreed.
 */
class ApartmentPriceConsistencyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_apartment_created_without_a_price_gets_the_matrix_suggested_price(): void
    {
        $user = User::query()->where('legacy_wp_id', 3)->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->postJson('/api/apartments', [
            'building_id' => 476,
            'district_id' => 475,
            'apartment_type' => '2BR',
            'quality_standard' => 'standard',
            'room_number' => 'PriceConsistencyTest-'.uniqid(),
            'facilities' => [],
            'status' => 'active',
        ]);

        $response->assertCreated();

        $apartmentId = $response->json('data.id');
        $priceDaily = (float) $response->json('data.price_daily');

        $this->assertGreaterThan(0, $priceDaily);

        $building = Building::query()->find(476);
        $expected = app(PriceMatrixService::class)->suggestApartmentPrice($building, '2BR', 'standard');

        $this->assertEquals($expected, (int) $priceDaily);

        $suggestedResponse = $this->actingAs($user)->getJson("/api/apartments/{$apartmentId}/suggested-price");
        $suggestedResponse->assertOk();
        $this->assertEquals($expected, $suggestedResponse->json('data.suggested_price'));
    }
}
