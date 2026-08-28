<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApartmentStoreTest extends TestCase
{
    public function test_authenticated_user_can_create_apartment_via_api(): void
    {
        $user = User::query()->where('legacy_wp_id', 3)->first()
            ?? User::query()->first();

        $this->assertNotNull($user);

        $response = $this->actingAs($user)->postJson('/api/apartments', [
            'building_id' => 476,
            'district_id' => 475,
            'apartment_type' => '2BR',
            'quality_standard' => 'above_average',
            'distinguishing_feature' => 'city view',
            'room_number' => '453',
            'about_this_short' => 'test via feature test',
            'facilities' => [9, 6],
            'price_daily' => 2200000,
            'status' => 'draft',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.id', fn ($id) => $id > 0);
    }
}
