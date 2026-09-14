<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class BuildingAdminTest extends TestCase
{
    public function test_superadmin_can_create_and_archive_a_building(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $district = District::query()->firstOrFail();
        $shortName = 'Twr'.Str::random(8);

        $create = $this->actingAs($superadmin)->postJson('/api/admin/buildings', [
            'name' => 'Test Tower '.Str::random(8),
            'short_name' => $shortName,
            'district_id' => $district->district_id,
        ]);

        $create->assertCreated();
        $buildingId = $create->json('data.id');

        $this->assertDatabaseHas('buildings', [
            'id' => $buildingId,
            'short_name' => $shortName,
            'status' => 'publish',
        ]);

        $archive = $this->actingAs($superadmin)->patchJson("/api/admin/buildings/{$buildingId}/archive", [
            'archived' => true,
        ]);

        $archive->assertOk();
        $this->assertDatabaseHas('buildings', ['id' => $buildingId, 'status' => 'archived']);
    }

    public function test_duplicate_short_name_is_rejected(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $district = District::query()->firstOrFail();
        $shortName = 'Dup'.Str::random(8);

        $this->actingAs($superadmin)->postJson('/api/admin/buildings', [
            'name' => 'Tower A '.Str::random(8),
            'short_name' => $shortName,
            'district_id' => $district->district_id,
        ])->assertCreated();

        $this->actingAs($superadmin)->postJson('/api/admin/buildings', [
            'name' => 'Tower B '.Str::random(8),
            'short_name' => $shortName,
            'district_id' => $district->district_id,
        ])->assertUnprocessable();
    }
}
