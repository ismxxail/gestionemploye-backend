<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // LOGIN
    // ============================================================

    /** @test */
    public function login_returns_token_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email'    => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role'     => 'admin',
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    /** @test */
    public function login_fails_with_wrong_password()
    {
        User::factory()->create([
            'email'    => 'admin@test.com',
            'password' => bcrypt('correctpassword'),
            'role'     => 'admin',
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Email ou mot de passe incorrect ']);
    }

    /** @test */
    public function login_fails_with_nonexistent_email()
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'notexist@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    // ============================================================
    // LOGOUT
    // ============================================================

    /** @test */
    public function admin_can_logout()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Déconnexion réussie']);
    }

    /** @test */
    public function employeur_can_logout()
    {
        $employeur = User::factory()->create(['role' => 'employeur']);
        $token = $employeur->createToken('token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertStatus(200);
    }

    /** @test */
    public function logout_requires_authentication()
    {
        // No token provided → should be rejected
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }
}
