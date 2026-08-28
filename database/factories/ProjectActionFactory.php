<?php

namespace Database\Factories;

use App\Models\ProjectTracking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectAction>
 */
class ProjectActionFactory extends Factory
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
            'title' => fake()->sentence(3),
            'responsible_name' => fake()->name(),
            'due_date' => now()->addWeek()->toDateString(),
            'priority' => 'normal',
            'status' => 'open',
        ];
    }
}
