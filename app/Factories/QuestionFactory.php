<?php

namespace App\Factories;

use App\Models\Question;
use App\Models\MultipleChoiceQuestion;
use App\Models\TrueOrFalseQuestion;
use App\Models\IdentificationQuestion;
use App\Models\RankingQuestion;
use App\Models\MatchingQuestion;
use Illuminate\Support\Facades\DB;

class QuestionFactory
{
    public static function create(array $data)
    {
        $question_data = [
            'topic_id' => (int) $data['topic'],
            'question_type' => $data['type'],
            'name' => $data['name'],
            'points' => $data['points']
        ];
        $question_type_data = [
            'items' => $data['items'] ?? [],
            'solution' => $data['solution']
        ];    

        \Log::info('Question Data', $question_data);
        \Log::info('Question Type Data', $question_type_data);
        
        DB::beginTransaction();

        try {
            $question = Question::create($question_data);

            switch ($question_data['question_type']) {
                case 'multiple_choice':
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
                    DB::commit();
                    break;
                case 'true_or_false':
                    \Log::info('True or false solution', [ $question_type_data['solution']]);
                    TrueOrFalseQuestion::create([
                        'question_id' => $question->id,
                        'solution' => $question_type_data['solution']
                    ]);
                    break;
                case 'identification':
                    \Log::info('Identification solution', [ $question_type_data['solution']]);
                    IdentificationQuestion::create([
                        'question_id' => $question->id,
                        'solution' => $question_type_data['solution']
                    ]);
                    break;

                case 'ranking':
                    switch ($question_type_data['solution']){
                        case 'ascending':
                            foreach($question_type_data['items'] as $order => $item){
                            $order += 1;
                            \Log::info('Ranking ascending solution items', ['order' => $order, 'item' => $item]);
                            }                   
                            break;

                        case 'descending':
                            $descending_solution = array_reverse($question_type_data['items']);
                            foreach($descending_solution as $order => $item){
                                $order += 1;
                                \Log::info('Ranking ascending solution items', ['order' => $order, 'item' => $item]);
                            } 
                            break;

                        default:
                        throw new \Exception("Unknown ranking solution: {$question_type_data['solution']}");
                        
                    }
                    // RankingQuestion::create($data);
                    break;

                case 'matching':
                    foreach($question_type_data['items'] as $item){
                        \Log::info('Question Type items', [$item]);
                    }
                    // MatchingQuestion::create($data);
                    break;

                default:
                    throw new \Exception("Unknown question type: {$question_data['question_type']}");
            }
        } catch (\Exception $e) {
            DB::rollBack(); 
            throw $e;
        }
    }
}
