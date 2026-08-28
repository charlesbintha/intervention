<?php

namespace Database\Factories;

use App\Models\ProjectTracking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectBlocker>
 */
class ProjectBlockerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_tracking_id' => ProjectTracking::factory(),
            'user_id' => User::factory(),
            'category' => 'Technique',
            'description' => fake()->sentence(),
            'severity' => 'medium',
            'status' => 'open',
            'opened_at' => now()->toDateString(),
        ];
    }
}
