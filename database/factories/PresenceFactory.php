<?php

namespace Database\Factories;

use App\Models\Presence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Presence>
 */
class PresenceFactory extends Factory
{
    protected $model = Presence::class;

    public function definition(): array
    {
        return [
            'employeur_id' => User::factory()->create(['role' => 'employeur'])->id,
            'date'         => Carbon::now()->toDateString(),
            'check_in'     => '08:00:00',
            'check_out'    => null,
        ];
    }
}
