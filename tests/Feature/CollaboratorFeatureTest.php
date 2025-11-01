<?php

namespace Tests\Feature;

use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CollaboratorFeatureTest extends TestCase
{
    public function test_user_can_list_own_collaborators(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Collaborator::factory()->count(3)->create(['user_id' => $user->id]);
        Collaborator::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/collaborators', $this->authHeaders($this->authenticateUser($user)));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'cpf', 'city', 'state'],
                ],
                'meta',
                'links',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_user_can_search_collaborators_by_name(): void
    {
        $user = User::factory()->create();

        Collaborator::factory()->create([
            'user_id' => $user->id,
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        Collaborator::factory()->create([
            'user_id' => $user->id,
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
        ]);

        $token = $this->authenticateUser($user);

        $response = $this->getJson('/api/collaborators?search=João', $this->authHeaders($token));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('João Silva', $response->json('data.0.name'));
    }

    public function test_user_can_search_collaborators_by_email(): void
    {
        $user = User::factory()->create();

        Collaborator::factory()->create([
            'user_id' => $user->id,
            'email' => 'joao@example.com',
        ]);

        Collaborator::factory()->create([
            'user_id' => $user->id,
            'email' => 'maria@example.com',
        ]);

        $token = $this->authenticateUser($user);

        $response = $this->getJson('/api/collaborators?search=maria', $this->authHeaders($token));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('maria@example.com', $response->json('data.0.email'));
    }

    public function test_user_can_sort_collaborators(): void
    {
        $user = User::factory()->create();

        Collaborator::factory()->create([
            'user_id' => $user->id,
            'name' => 'Zebra',
            'email' => 'zebra@example.com',
        ]);

        Collaborator::factory()->create([
            'user_id' => $user->id,
            'name' => 'Alice',
            'email' => 'alice@example.com',
        ]);

        $token = $this->authenticateUser($user);

        $response = $this->getJson('/api/collaborators?sort_by=name&sort_dir=asc', $this->authHeaders($token));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Alice', $data[0]['name']);
        $this->assertEquals('Zebra', $data[1]['name']);
    }

    public function test_user_can_paginate_collaborators(): void
    {
        $user = User::factory()->create();
        Collaborator::factory()->count(15)->create(['user_id' => $user->id]);

        $token = $this->authenticateUser($user);

        $response = $this->getJson('/api/collaborators?per_page=10', $this->authHeaders($token));

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_user_can_create_collaborator(): void
    {
        $user = User::factory()->create();
        $token = $this->authenticateUser($user);

        $response = $this->postJson('/api/collaborators', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => '12345678901',
            'city' => 'São Paulo',
            'state' => 'SP',
        ], $this->authHeaders($token));

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'cpf', 'city', 'state'],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'João Silva',
                    'email' => 'joao@example.com',
                    'cpf' => '12345678901',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                ],
            ]);

        $this->assertDatabaseHas('collaborators', [
            'user_id' => $user->id,
            'email' => 'joao@example.com',
            'cpf' => '12345678901',
        ]);
    }

    public function test_user_cannot_create_collaborator_without_authentication(): void
    {
        $response = $this->postJson('/api/collaborators', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => '12345678901',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_create_collaborator_with_invalid_data(): void
    {
        $token = $this->authenticateUser();

        $response = $this->postJson('/api/collaborators', [
            'name' => '',
            'email' => 'email-invalido',
            'cpf' => '123',
            'city' => '',
            'state' => 'XYZ',
        ], $this->authHeaders($token));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'cpf', 'city', 'state']);
    }

    public function test_user_cannot_create_collaborator_with_duplicate_email(): void
    {
        $user = User::factory()->create();
        Collaborator::factory()->create([
            'user_id' => $user->id,
            'email' => 'joao@example.com',
        ]);

        $token = $this->authenticateUser($user);

        $response = $this->postJson('/api/collaborators', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => '12345678901',
            'city' => 'São Paulo',
            'state' => 'SP',
        ], $this->authHeaders($token));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_view_own_collaborator(): void
    {
        $user = User::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'user_id' => $user->id,
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $token = $this->authenticateUser($user);

        $response = $this->getJson("/api/collaborators/{$collaborator->id}", $this->authHeaders($token));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'cpf', 'city', 'state'],
            ])
            ->assertJson([
                'data' => [
                    'id' => $collaborator->id,
                    'name' => 'João Silva',
                    'email' => 'joao@example.com',
                ],
            ]);
    }

    public function test_user_cannot_view_other_user_collaborator(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $collaboratorB = Collaborator::factory()->create([
            'user_id' => $userB->id,
        ]);

        $token = $this->authenticateUser($userA);

        $response = $this->getJson("/api/collaborators/{$collaboratorB->id}", $this->authHeaders($token));

        $response->assertStatus(404);
    }

    public function test_user_can_update_own_collaborator(): void
    {
        $user = User::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'user_id' => $user->id,
            'name' => 'João Silva',
            'city' => 'São Paulo',
        ]);

        $token = $this->authenticateUser($user);

        $response = $this->putJson("/api/collaborators/{$collaborator->id}", [
            'name' => 'João Santos',
            'city' => 'Rio de Janeiro',
        ], $this->authHeaders($token));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'João Santos',
                    'city' => 'Rio de Janeiro',
                ],
            ]);

        $this->assertDatabaseHas('collaborators', [
            'id' => $collaborator->id,
            'name' => 'João Santos',
            'city' => 'Rio de Janeiro',
        ]);
    }

    public function test_user_can_partially_update_collaborator(): void
    {
        $user = User::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'user_id' => $user->id,
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'city' => 'São Paulo',
        ]);

        $token = $this->authenticateUser($user);

        $response = $this->patchJson("/api/collaborators/{$collaborator->id}", [
            'city' => 'Rio de Janeiro',
        ], $this->authHeaders($token));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'João Silva',
                    'email' => 'joao@example.com',
                    'city' => 'Rio de Janeiro',
                ],
            ]);
    }

    public function test_user_cannot_update_other_user_collaborator(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $collaboratorB = Collaborator::factory()->create([
            'user_id' => $userB->id,
        ]);

        $token = $this->authenticateUser($userA);

        $response = $this->putJson("/api/collaborators/{$collaboratorB->id}", [
            'name' => 'Nome Alterado',
        ], $this->authHeaders($token));

        $response->assertStatus(404);
    }

    public function test_user_can_delete_own_collaborator(): void
    {
        $user = User::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'user_id' => $user->id,
        ]);

        $token = $this->authenticateUser($user);

        $response = $this->deleteJson("/api/collaborators/{$collaborator->id}", [], $this->authHeaders($token));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Collaborator has been deleted successfully',
            ]);

        $this->assertDatabaseMissing('collaborators', [
            'id' => $collaborator->id,
        ]);
    }

    public function test_user_cannot_delete_other_user_collaborator(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $collaboratorB = Collaborator::factory()->create([
            'user_id' => $userB->id,
        ]);

        $token = $this->authenticateUser($userA);

        $response = $this->deleteJson("/api/collaborators/{$collaboratorB->id}", [], $this->authHeaders($token));

        $response->assertStatus(404);

        $this->assertDatabaseHas('collaborators', [
            'id' => $collaboratorB->id,
        ]);
    }

    public function test_user_can_import_collaborators_file(): void
    {
        Storage::fake('local');
        Bus::fake();

        $user = User::factory()->create();
        $token = $this->authenticateUser($user);

        $file = UploadedFile::fake()->create('colaboradores.csv', 100, 'text/csv');

        $response = $this->postJson('/api/collaborators/import', [
            'file' => $file,
        ], $this->authHeaders($token));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Arquivo recebido. O processamento será feito em segundo plano.',
            ]);

        Storage::assertExists('imports/' . $file->hashName());
    }

    public function test_user_cannot_import_invalid_file_type(): void
    {
        $user = User::factory()->create();
        $token = $this->authenticateUser($user);

        $file = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/collaborators/import', [
            'file' => $file,
        ], $this->authHeaders($token));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_user_cannot_import_file_without_authentication(): void
    {
        $file = UploadedFile::fake()->create('colaboradores.csv', 100, 'text/csv');

        $response = $this->postJson('/api/collaborators/import', [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_access_collaborators_without_authentication(): void
    {
        $response = $this->getJson('/api/collaborators');

        $response->assertStatus(401);
    }
}
