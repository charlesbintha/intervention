<?php

namespace Database\Factories;

use App\Models\ProjectActivity;
use App\Models\ProjectTracking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkLog>
 */
class WorkLogFactory extends Factory
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
            'project_activity_id' => ProjectActivity::factory(),
            'user_id' => User::factory(),
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHour()->format('H:i:s'),
            'end_time' => now()->format('H:i:s'),
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'quantity_completed' => 10,
            'work_description' => fake()->sentence(),
        ];
    }
}
