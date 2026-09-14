<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\BuildingPricingFactor;
use App\Models\User;
use Tests\TestCase;

class PriceMatrixAdminTest extends TestCase
{
    public function test_superadmin_can_list_price_matrix_grouped_by_district(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->getJson('/api/admin/price-matrix');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => ['columns', 'standard_factors', 'districts'],
        ]);
    }

    public function test_superadmin_can_save_a_price_matrix_cell(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $building = Building::query()->whereNotNull('district_id')->firstOrFail();

        $response = $this->actingAs($superadmin)->patchJson('/api/admin/price-matrix', [
            'changes' => [
                ['building_id' => $building->id, 'type_key' => 'Studio', 'price_vnd' => 950_000],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('building_pricing_factors', [
            'building_id' => $building->id,
            'type_key' => 'Studio',
            'price_override_vnd' => 950_000,
        ]);
    }

    public function test_saving_an_existing_cell_updates_it_instead_of_duplicating(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $building = Building::query()->whereNotNull('district_id')->firstOrFail();

        $payload = fn (int $price) => [
            'changes' => [
                ['building_id' => $building->id, 'type_key' => '1BR', 'price_vnd' => $price],
            ],
        ];

        $this->actingAs($superadmin)->patchJson('/api/admin/price-matrix', $payload(800_000))->assertOk();
        $rowsAfterFirstSave = BuildingPricingFactor::query()
            ->where('building_id', $building->id)->where('type_key', '1BR')->count();

        $this->actingAs($superadmin)->patchJson('/api/admin/price-matrix', $payload(1_000_000))->assertOk();

        $this->assertSame(1, $rowsAfterFirstSave);
        $this->assertSame(
            1,
            BuildingPricingFactor::query()->where('building_id', $building->id)->where('type_key', '1BR')->count(),
        );
        $this->assertDatabaseHas('building_pricing_factors', [
            'building_id' => $building->id,
            'type_key' => '1BR',
            'price_override_vnd' => 1_000_000,
        ]);
    }
}
