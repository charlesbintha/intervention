<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TextRevision>
 */
class TextRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reviewable_type' => \App\Models\Survey::class,
            'reviewable_id' => \App\Models\Survey::factory(),
            'user_id' => \App\Models\User::factory(),
            'field' => 'purpose',
            'original_text' => 'Reunion de démarrage.',
            'corrected_text' => 'Réunion de démarrage.',
            'original_snapshot' => ['purpose' => 'Reunion de démarrage.'],
            'model_name' => 'test-model',
            'after_validation' => false,
        ];
    }
}
