<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApartmentReviewTest extends TestCase
{
    use DatabaseTransactions;

    private function createHost(): User
    {
        $host = User::factory()->create(['role' => 'host']);
        $host->update(['legacy_wp_id' => 900_000 + $host->id]);

        return $host;
    }

    private function createApartment(User $host, array $overrides = []): Apartment
    {
        $response = $this->actingAs($host)->postJson('/api/apartments', $overrides + [
            'building_id' => 476,
            'district_id' => 475,
            'apartment_type' => '2BR',
            'name' => 'Review test apartment',
            'room_number' => 'ReviewTest-'.uniqid(),
            'description' => 'A bright two bedroom apartment.',
            'images' => [['thumb' => 'https://example.com/photo.jpg', 'image_id' => '', 'caption' => '']],
            'price_daily' => 900000,
        ]);

        $response->assertCreated();

        return Apartment::query()->findOrFail($response->json('data.id'));
    }

    private function publicIds(): array
    {
        return collect($this->getJson('/api/public/apartments')->json('data'))->pluck('id')->all();
    }

    public function test_a_host_cannot_publish_their_own_apartment(): void
    {
        $host = $this->createHost();

        $apartment = $this->createApartment($host, ['status' => 'active']);
        $this->assertSame('draft', $apartment->status);

        $this->actingAs($host)->putJson("/api/apartments/{$apartment->ID}", ['status' => 'active'])->assertOk();
        $this->assertSame('draft', $apartment->fresh()->status);

        $this->actingAs($host)->postJson("/api/apartments/{$apartment->ID}/approve")->assertForbidden();
        $this->assertNotContains($apartment->ID, $this->publicIds());
    }

    public function test_submit_then_approve_puts_the_apartment_on_the_website(): void
    {
        $host = $this->createHost();
        $admin = User::factory()->create(['role' => 'superadmin']);
        $apartment = $this->createApartment($host);

        $this->actingAs($host)->postJson("/api/apartments/{$apartment->ID}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending');
        $this->assertNotContains($apartment->ID, $this->publicIds());

        $this->actingAs($admin)->postJson("/api/apartments/{$apartment->ID}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'active');
        $this->assertContains($apartment->ID, $this->publicIds());

        $this->actingAs($admin)->postJson("/api/apartments/{$apartment->ID}/unpublish")->assertOk();
        $this->assertSame('draft', $apartment->fresh()->status);
    }

    public function test_supervisor_can_send_an_apartment_back(): void
    {
        $host = $this->createHost();
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $apartment = $this->createApartment($host);

        $this->actingAs($host)->postJson("/api/apartments/{$apartment->ID}/submit")->assertOk();

        $this->actingAs($supervisor)->postJson("/api/apartments/{$apartment->ID}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_an_incomplete_apartment_cannot_be_submitted(): void
    {
        $host = $this->createHost();
        $apartment = $this->createApartment($host, ['description' => '', 'images' => [], 'price_daily' => 0]);
        // Creation fills a price from the matrix when none is given.
        $apartment->forceFill(['price_daily' => 0])->save();

        $this->actingAs($host)->postJson("/api/apartments/{$apartment->ID}/submit")
            ->assertUnprocessable()
            ->assertJsonPath('missing', ['description', 'photos', 'daily price']);
        $this->assertSame('draft', $apartment->fresh()->status);
    }

    public function test_a_host_cannot_submit_someone_elses_apartment(): void
    {
        $apartment = $this->createApartment($this->createHost());

        $this->actingAs($this->createHost())->postJson("/api/apartments/{$apartment->ID}/submit")->assertForbidden();
    }
}
