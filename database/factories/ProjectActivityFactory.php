<?php

namespace Database\Factories;

use App\Models\ProjectTracking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectActivity>
 */
class ProjectActivityFactory extends Factory
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
            'lot_name' => 'Travaux techniques',
            'name' => fake()->sentence(3),
            'assigned_agents' => [fake()->name()],
            'current_start_date' => now()->startOfDay(),
            'current_end_date' => now()->addWeek()->startOfDay(),
            'unit' => 'pourcentage',
            'planned_quantity' => 100,
            'completed_quantity' => 0,
            'status' => 'not_started',
            'priority' => 'normal',
        ];
    }
}
