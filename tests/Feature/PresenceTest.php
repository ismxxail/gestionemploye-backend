<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Presence;
use App\Models\WorkDay;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PresenceTest extends TestCase
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
    // ADMIN: GET /api/presences
    // ============================================================

    /** @test */
    public function admin_can_list_all_presences()
    {
        [, $token] = $this->adminToken();
        [$emp]     = $this->employeurToken();

        Presence::factory()->count(4)->create(['employeur_id' => $emp->id]);

        $response = $this->withToken($token)->getJson('/api/presences');

        $response->assertStatus(200)
                 ->assertJsonStructure(['presences'])
                 ->assertJsonCount(4, 'presences');
    }

    /** @test */
    public function employeur_cannot_list_all_presences()
    {
        [, $token] = $this->employeurToken();

        $response = $this->withToken($token)->getJson('/api/presences');

        $response->assertStatus(403);
    }

    // ============================================================
    // EMPLOYEUR: GET /api/presence/{employeurId}
    // ============================================================

    /** @test */
    public function employeur_can_get_his_own_presences()
    {
        [$emp, $token] = $this->employeurToken();

        Presence::factory()->count(3)->create(['employeur_id' => $emp->id]);

        $response = $this->withToken($token)->getJson("/api/presence/{$emp->id}");

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'presences');
    }

    // ============================================================
    // CHECK-IN: POST /api/presence/checkin/{employeurId}
    // ============================================================

    /** @test */
    public function employeur_can_check_in()
    {
        [$emp, $token] = $this->employeurToken();

        $response = $this->withToken($token)->postJson("/api/presence/checkin/{$emp->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Check-in successful'])
                 ->assertJsonStructure(['presence' => ['id', 'employeur_id', 'date', 'check_in']]);

        $this->assertDatabaseHas('presences', [
            'employeur_id' => $emp->id,
            'date'         => Carbon::now()->toDateString(),
        ]);
    }

    /** @test */
    public function second_check_in_same_day_does_not_duplicate_record()
    {
        [$emp, $token] = $this->employeurToken();

        // First check-in
        $this->withToken($token)->postJson("/api/presence/checkin/{$emp->id}");

        // Second check-in on same day
        $this->withToken($token)->postJson("/api/presence/checkin/{$emp->id}");

        // Only one presence row should exist for today
        $this->assertDatabaseCount('presences', 1);
    }

    /** @test */
    public function admin_cannot_check_in()
    {
        [, $token] = $this->adminToken();

        $response = $this->withToken($token)->postJson('/api/presence/checkin/1');

        $response->assertStatus(403);
    }

    // ============================================================
    // CHECK-OUT: POST /api/presence/checkout/{employeurId}
    // ============================================================

    /** @test */
    public function employeur_can_check_out_after_check_in()
    {
        [$emp, $token] = $this->employeurToken();

        // Create a presence with check-in but no check-out
        Presence::factory()->create([
            'employeur_id' => $emp->id,
            'date'         => Carbon::now()->toDateString(),
            'check_in'     => '08:00:00',
            'check_out'    => null,
        ]);

        $response = $this->withToken($token)->postJson("/api/presence/checkout/{$emp->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Check-out successful']);
    }

    /** @test */
    public function checkout_returns_404_when_no_checkin_today()
    {
        [$emp, $token] = $this->employeurToken();

        // No presence row at all
        $response = $this->withToken($token)->postJson("/api/presence/checkout/{$emp->id}");

        $response->assertStatus(404)
                 ->assertJson(['message' => 'No check-in found for today']);
    }

    /** @test */
    public function checkout_returns_409_when_already_checked_out()
    {
        [$emp, $token] = $this->employeurToken();

        // Presence with both check-in and check-out already set
        Presence::factory()->create([
            'employeur_id' => $emp->id,
            'date'         => Carbon::now()->toDateString(),
            'check_in'     => '08:00:00',
            'check_out'    => '17:00:00',
        ]);

        $response = $this->withToken($token)->postJson("/api/presence/checkout/{$emp->id}");

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Already checked out today']);
    }

    // ============================================================
    // WORK HOURS: GET /api/workhours/today
    // ============================================================

    /** @test */
    public function employeur_can_get_todays_work_hours()
    {
        [, $token] = $this->employeurToken();

        $todayName = Carbon::now()->format('l'); // e.g. "Monday"

        WorkDay::create([
            'day_name'   => $todayName,
            'start_time' => '08:00:00',
            'end_time'   => '17:00:00',
        ]);

        $response = $this->withToken($token)->getJson('/api/workhours/today');

        $response->assertStatus(200)
                 ->assertJsonStructure(['day', 'start', 'end'])
                 ->assertJsonPath('day', $todayName);
    }

    /** @test */
    public function work_hours_returns_404_when_no_work_day_defined()
    {
        [, $token] = $this->employeurToken();

        // No WorkDay rows in DB
        $response = $this->withToken($token)->getJson('/api/workhours/today');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'No working hours defined for today']);
    }

    /** @test */
    public function admin_cannot_access_work_hours_today()
    {
        [, $token] = $this->adminToken();

        $response = $this->withToken($token)->getJson('/api/workhours/today');

        $response->assertStatus(403);
    }
}
