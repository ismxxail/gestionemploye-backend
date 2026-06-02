<?php

namespace Database\Factories;

use App\Models\Conge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conge>
 */
class CongeFactory extends Factory
{
    protected $model = Conge::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 week', '+2 weeks');
        $end   = $this->faker->dateTimeBetween($start, '+3 weeks');

        return [
            'employeur_id'  => User::factory()->create(['role' => 'employeur'])->id,
            'start_date'    => $start->format('Y-m-d'),
            'end_date'      => $end->format('Y-m-d'),
            'reason'        => $this->faker->optional()->sentence(),
            'status'        => 'En attente',
            'admin_comment' => null,
        ];
    }
}
