<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectTracking>
 */
class ProjectTrackingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'external_project_code' => fake()->unique()->bothify('PRJ-####'),
            'external_project_name' => fake()->sentence(3),
            'subsidiary' => fake()->randomElement(['GUT', 'CP', 'UTA', 'UA', 'UTE', 'UC']),
            'client_name' => fake()->company(),
            'location' => fake()->city(),
            'current_start_date' => now()->startOfDay(),
            'current_end_date' => now()->addMonth()->startOfDay(),
            'status' => 'draft',
        ];
    }
}
