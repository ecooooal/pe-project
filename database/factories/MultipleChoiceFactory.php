<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MultipleChoiceQuestion>
 */
class MultipleChoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'choice_key' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'item' => $this->faker->word(), // Random word as the choice
            'is_correct' => $this->faker->boolean(), // Randomly true or false
        ];
    }
}
