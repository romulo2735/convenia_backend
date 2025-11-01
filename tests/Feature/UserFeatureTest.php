<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserFeatureTest extends TestCase
{
    public function test_authenticated_user_can_get_own_profile(): void
    {
       $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $token = $this->authenticateUser($user);

        $response = $this->getJson('/api/user', $this->authHeaders($token));

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'email'])
            ->assertJson([
                'id' => $user->id,
                'name' => 'João Silva',
                'email' => 'joao@example.com',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_user_cannot_access_profile_with_invalid_token(): void
    {
        $response = $this->getJson('/api/user', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer invalid-token-here',
        ]);

        $response->assertStatus(401);
    }
}
