<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeurTest extends TestCase
{
    use RefreshDatabase;

    // Helper: create admin and return token
    private function adminToken(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('token')->plainTextToken;
        return [$admin, $token];
    }

    // Helper: create employeur and return token
    private function employeurToken(): array
    {
        $emp = User::factory()->create(['role' => 'employeur']);
        $token = $emp->createToken('token')->plainTextToken;
        return [$emp, $token];
    }

    // ============================================================
    // INDEX
    // ============================================================

    /** @test */
    public function admin_can_list_employeurs()
    {
        [, $token] = $this->adminToken();

        // Create 3 employeurs
        User::factory()->count(3)->create(['role' => 'employeur']);

        $response = $this->withToken($token)->getJson('/api/employeurs');

        $response->assertStatus(200)
                 ->assertJsonStructure(['employeurs'])
                 ->assertJsonCount(3, 'employeurs');
    }

    /** @test */
    public function employeur_cannot_list_employeurs()
    {
        [, $token] = $this->employeurToken();

        $response = $this->withToken($token)->getJson('/api/employeurs');

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_employeurs()
    {
        $response = $this->getJson('/api/employeurs');

        $response->assertStatus(401);
    }

    // ============================================================
    // STORE
    // ============================================================

    /** @test */
    public function admin_can_create_employeur()
    {
        [, $token] = $this->adminToken();

        $response = $this->withToken($token)->postJson('/api/employeurs', [
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('employeur.name', 'John Doe')
                 ->assertJsonPath('employeur.role', 'employeur');

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    /** @test */
    public function create_employeur_fails_with_duplicate_email()
    {
        [, $token] = $this->adminToken();

        User::factory()->create(['email' => 'existing@example.com', 'role' => 'employeur']);

        $response = $this->withToken($token)->postJson('/api/employeurs', [
            'name'     => 'Another',
            'email'    => 'existing@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function create_employeur_fails_with_short_password()
    {
        [, $token] = $this->adminToken();

        $response = $this->withToken($token)->postJson('/api/employeurs', [
            'name'     => 'Test',
            'email'    => 'test@example.com',
            'password' => '123',     // less than 8 chars
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function create_employeur_fails_when_fields_are_missing()
    {
        [, $token] = $this->adminToken();

        $response = $this->withToken($token)->postJson('/api/employeurs', []);

        $response->assertStatus(422);
    }

    // ============================================================
    // SHOW
    // ============================================================

    /** @test */
    public function admin_can_show_single_employeur()
    {
        [, $token] = $this->adminToken();

        $emp = User::factory()->create(['role' => 'employeur']);

        $response = $this->withToken($token)->getJson("/api/employeurs/{$emp->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('employeur.id', $emp->id);
    }

    /** @test */
    public function show_returns_404_for_nonexistent_employeur()
    {
        [, $token] = $this->adminToken();

        $response = $this->withToken($token)->getJson('/api/employeurs/9999');

        $response->assertStatus(404);
    }

    /** @test */
    public function show_returns_404_for_admin_user_with_employeur_role_check()
    {
        [, $token] = $this->adminToken();

        // Create an admin — looking up by ID should return 404 because role != 'employeur'
        $admin2 = User::factory()->create(['role' => 'admin']);

        $response = $this->withToken($token)->getJson("/api/employeurs/{$admin2->id}");

        $response->assertStatus(404);
    }

    // ============================================================
    // UPDATE
    // ============================================================

    /** @test */
    public function admin_can_update_employeur()
    {
        [, $token] = $this->adminToken();

        $emp = User::factory()->create(['role' => 'employeur']);

        $response = $this->withToken($token)->putJson("/api/employeurs/{$emp->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('employeur.name', 'Updated Name');
    }

    /** @test */
    public function update_returns_404_for_nonexistent_employeur()
    {
        [, $token] = $this->adminToken();

        $response = $this->withToken($token)->putJson('/api/employeurs/9999', [
            'name' => 'Ghost',
        ]);

        $response->assertStatus(404);
    }

    // ============================================================
    // DESTROY
    // ============================================================

    /** @test */
    public function admin_can_delete_employeur()
    {
        [, $token] = $this->adminToken();

        $emp = User::factory()->create(['role' => 'employeur']);

        $response = $this->withToken($token)->deleteJson("/api/employeurs/{$emp->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Employeur deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $emp->id]);
    }

    /** @test */
    public function delete_returns_404_for_nonexistent_employeur()
    {
        [, $token] = $this->adminToken();

        $response = $this->withToken($token)->deleteJson('/api/employeurs/9999');

        $response->assertStatus(404);
    }
}
