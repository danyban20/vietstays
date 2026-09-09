<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LoginLegacyPasswordTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Users imported from WordPress store a $wp$/$P$/$H$ formatted hash, not
     * plain Bcrypt. Hash::check() throws for those (hashing.bcrypt.verify is
     * on by default) instead of returning false — verifyPassword() must catch
     * that and fall through to WordPressPasswordVerifier, not 500.
     */
    public function test_wrong_password_against_a_legacy_hash_returns_422_not_500(): void
    {
        $user = User::factory()->create([
            'password' => '$wp$2y$10$usxin0FhLDBRUS/pTeYnkuUM.frbNS.qFq9zEDxu2Cwb3M1t8Da1G',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'definitely-wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.email.0', 'These credentials do not match our records.');
    }
}
