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
    
        $data = [
            'topic_id' => Topic::inRandomOrder()->first()->id,
            'question_type' => $questionType,
            'name' => $this->faker->sentence(),
            'points' => $this->faker->numberBetween(1, 10),
        ];
        switch ($questionType):
            case 'multiple_choice':
                // generate four multiple choice model and attach them to this question id
                $choice_keys = ['a','b','c','d'];
                $question_type_data['items'] = array_combine($choice_keys,  $question_type_data['items']);
                foreach($question_type_data['items'] as $key => $item){
                    MultipleChoiceQuestion::create([
                        'question_id' => $question->id,
                        'choice_key' => $key,
                        'item' => $item,
                        'is_correct' => $key == $question_type_data['solution']
                    ]);
                }  
                break;
            case 'true_or_false':
                // generate one true or false model and attach them to this question id

                break;

            case 'identification':
                // generate one identification model and attach them to this question id

                break;

        return $data;
    }
}
