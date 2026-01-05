<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InterventionUte>
 */
class InterventionUteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDatetime = fake()->dateTimeBetween('now', '+1 month');
        $endDatetime = (clone $startDatetime)->modify('+2 hours');

        return [
            'user_id' => \App\Models\User::factory(),
            'company_name' => fake()->company(),
            'location' => fake()->address(),
            'contact_name' => fake()->name(),
            'contact_function' => fake()->jobTitle(),
            'contact_phone' => fake()->phoneNumber(),
            'contact_email' => fake()->safeEmail(),
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'purpose' => fake()->paragraph(),
            'diagnostic' => fake()->randomElement(['cablage', 'wifi', 'FAI', 'electricite', 'autre']),
            'type' => fake()->randomElement(['changement_piece', 'entretien', 'depannage', 'autre']),
            'observations' => fake()->optional()->paragraph(),
            'status' => 'draft',
        ];
    }
}
