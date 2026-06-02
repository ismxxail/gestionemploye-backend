<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Conge;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CongeTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        return [$admin, $admin->createToken('token')->plainTextToken];
    }

    private function employeurToken(): array
    {
        $emp = User::factory()->create(['role' => 'employeur']);
        return [$emp, $emp->createToken('token')->plainTextToken];
    }

    // ============================================================
    // ADMIN: GET /api/conges
    // ============================================================

    /** @test */
    public function admin_can_list_all_conges()
    {
        [, $token]   = $this->adminToken();
        [$emp]       = $this->employeurToken();

        Conge::factory()->count(3)->create(['employeur_id' => $emp->id]);

        $response = $this->withToken($token)->getJson('/api/conges');

        $response->assertStatus(200)
                 ->assertJsonStructure(['conges'])
                 ->assertJsonCount(3, 'conges');
    }

    /** @test */
    public function employeur_cannot_access_admin_conge_list()
    {
        [, $token] = $this->employeurToken();

        $response = $this->withToken($token)->getJson('/api/conges');

        $response->assertStatus(403);
    }

    // ============================================================
    // EMPLOYEUR: POST /api/conge  (DemandeConge)
    // ============================================================

    /** @test */
    public function employeur_can_submit_conge_request()
    {
        [$emp, $token] = $this->employeurToken();

        $response = $this->withToken($token)->postJson('/api/conge', [
            'employeur_id' => $emp->id,
            'start_date'   => '2026-07-01',
            'end_date'     => '2026-07-10',
            'reason'       => 'Vacances',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('conge.status', 'En attente')
                 ->assertJsonPath('conge.employeur_id', $emp->id);

        $this->assertDatabaseHas('conges', [
            'employeur_id' => $emp->id,
            'start_date'   => '2026-07-01',
        ]);
    }

    /** @test */
    public function conge_request_fails_when_end_date_before_start_date()
    {
        [$emp, $token] = $this->employeurToken();

        $response = $this->withToken($token)->postJson('/api/conge', [
            'employeur_id' => $emp->id,
            'start_date'   => '2026-07-10',
            'end_date'     => '2026-07-01',  // before start
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function conge_request_fails_with_invalid_employeur_id()
    {
        [, $token] = $this->employeurToken();

        $response = $this->withToken($token)->postJson('/api/conge', [
            'employeur_id' => 99999,     // doesn't exist
            'start_date'   => '2026-07-01',
            'end_date'     => '2026-07-05',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function conge_request_is_accepted_with_same_start_and_end_date()
    {
        [$emp, $token] = $this->employeurToken();

        $response = $this->withToken($token)->postJson('/api/conge', [
            'employeur_id' => $emp->id,
            'start_date'   => '2026-07-01',
            'end_date'     => '2026-07-01',  // same day — allowed by after_or_equal
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function conge_request_works_without_reason_field()
    {
        [$emp, $token] = $this->employeurToken();

        $response = $this->withToken($token)->postJson('/api/conge', [
            'employeur_id' => $emp->id,
            'start_date'   => '2026-08-01',
            'end_date'     => '2026-08-05',
            // no reason
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('conge.reason', null);
    }

    /** @test */
    public function admin_cannot_submit_conge_request()
    {
        [$admin, $token] = $this->adminToken();

        $response = $this->withToken($token)->postJson('/api/conge', [
            'employeur_id' => $admin->id,
            'start_date'   => '2026-07-01',
            'end_date'     => '2026-07-05',
        ]);

        $response->assertStatus(403);
    }

    // ============================================================
    // ADMIN: PUT /api/conges/{conge}  (updateStatus)
    // ============================================================

    /** @test */
    public function admin_can_accept_conge()
    {
        [, $token] = $this->adminToken();
        [$emp]     = $this->employeurToken();

        $conge = Conge::factory()->create([
            'employeur_id' => $emp->id,
            'status'       => 'En attente',
        ]);

        $response = $this->withToken($token)->putJson("/api/conges/{$conge->id}", [
            'status'        => 'Accepter',
            'admin_comment' => 'Bon repos !',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('conge.status', 'Accepter');
    }

    /** @test */
    public function admin_can_refuse_conge()
    {
        [, $token] = $this->adminToken();
        [$emp]     = $this->employeurToken();

        $conge = Conge::factory()->create([
            'employeur_id' => $emp->id,
            'status'       => 'En attente',
        ]);

        $response = $this->withToken($token)->putJson("/api/conges/{$conge->id}", [
            'status' => 'Refuser',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('conge.status', 'Refuser');
    }

    /** @test */
    public function update_conge_status_fails_with_invalid_status()
    {
        [, $token] = $this->adminToken();
        [$emp]     = $this->employeurToken();

        $conge = Conge::factory()->create(['employeur_id' => $emp->id]);

        $response = $this->withToken($token)->putJson("/api/conges/{$conge->id}", [
            'status' => 'InvalidStatus',
        ]);

        $response->assertStatus(422);
    }

    // ============================================================
    // EMPLOYEUR: GET /api/conge/{employeurId}
    // ============================================================

    /** @test */
    public function employeur_can_get_his_own_conges()
    {
        [$emp, $token] = $this->employeurToken();

        Conge::factory()->count(2)->create(['employeur_id' => $emp->id]);

        $response = $this->withToken($token)->getJson("/api/conge/{$emp->id}");

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'conges');
    }

    /** @test */
    public function admin_cannot_access_conge_by_employeur_route()
    {
        [, $token] = $this->adminToken();

        $response = $this->withToken($token)->getJson('/api/conge/1');

        $response->assertStatus(403);
    }
}
