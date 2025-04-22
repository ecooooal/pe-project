<?php

namespace Database\Factories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questionType = $this->faker->randomElement(['multiple_choice', 'true_or_false', 'identification', 'ranking', 'matching']);
    
        return [
            'topic_id' => Topic::inRandomOrder()->first()->id,
            'question_type' => $questionType,
            'name' => $this->faker->sentence(),
            'points' => $this->faker->numberBetween(1, 10),
        ];
    }
}
