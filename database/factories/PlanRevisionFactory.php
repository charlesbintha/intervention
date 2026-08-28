<?php

namespace Database\Factories;

use App\Models\ProjectTracking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanRevision>
 */
class PlanRevisionFactory extends Factory
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
            'version' => 1,
            'reason' => fake()->sentence(),
        ];
    }
}
