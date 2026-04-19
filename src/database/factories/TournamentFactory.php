<?php

namespace Database\Factories;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tournament>
 */
class TournamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true) . ' Cup',
            'game' => fake()->randomElement(['Valorant', 'League of Legends', 'CS2', 'Fortnite']),
            'date' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'max_participants' => fake()->randomElement([4, 8, 16]),
            'status' => 'open',
            'format' => 'single_elimination',
            'organizer_id' => User::factory()->organizer(),
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => 'open']);
    }

    public function closed(): static
    {
        return $this->state(['status' => 'closed']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }
}
