<?php

namespace App\Factories;

use App\Models\Question;
use App\Services\QuestionTypeService;
use Illuminate\Support\Facades\DB;

class QuestionFactory
{

    public static function create(array $data)
    {
        $question_data = [
            'topic_id' => (int) $data['topic'],
            'question_type' => $data['type'],
            'total_points' => $data['points'],
            'name' => $data['name']
        ];   
       
        DB::transaction(function () use ($question_data, $data) {
            $question = Question::create($question_data);
            $question_type_service = new QuestionTypeService();
            if ($question_data['question_type'] == 'coding'){
                        $coding_question_data = [
                                    'instruction' => $data['instruction'],
                                    'syntax_points' => $data['syntax_points'],
                                    'runtime_points' => $data['runtime_points'],
                                    'test_case_points' => $data['test_case_points'],
                                ];
                            $coding_question_language_data = json_decode($data['supported_languages'], true);
                            $question_type_service->storeCoding(
                                    $question, $question_data, $coding_question_data, $coding_question_language_data
                            );
            } else {
                $question_type_data = [
                    'items' => $data['items'] ?? [],
                    'solution' => $data['solution'] ?? '',
                    'points' => $data['points'] ?? ''
                    ]; 
                match ($question_data['question_type']) {
                    'multiple_choice' => $question_type_service->storeMultipleChoice($question, $question_type_data),
                    'true_or_false' => $question_type_service->storeTrueOrFalse($question, $question_type_data),
                    'identification' => $question_type_service->storeIdentification($question, $question_type_data),
                    'ranking' => $question_type_service->storeRanking($question, $question_type_data),
                    'matching' => $question_type_service->storeMatching($question, $question_type_data),
                    default => throw new \InvalidArgumentException("Unknown question type: {$question_data['question_type']}"),
                };
            }
        });
    }

    public static function update(Question $question, array $data)
    {
        DB::transaction(function () use ($question, $data) {
            $previous_question_type = $question->question_type->value;
            $question_type_service = new QuestionTypeService();

            if ($data['type'] == 'coding'){
                $question_type_service->updateCoding($question, $data);
            } else {
                $question_type_data = [
                    'items' => $data['items'] ?? [],
                    'solution' => $data['solution'] ?? '',
                    'points' => $data['points'] ?? ''
                ]; 
                match ($data['type']) {
                    'multiple_choice' => $question_type_service->updateMultipleChoice($question, $question_type_data),
                    'true_or_false' => $question_type_service->updateTrueOrFalse($question, $question_type_data, $data, $previous_question_type),
                    'identification' => $question_type_service->updateIdentification($question, $question_type_data, $data,  $previous_question_type),
                    'ranking' => $question_type_service->updateRanking($question, $question_type_data),
                    'matching' => $question_type_service->updateMatching($question, $question_type_data),
                    default => throw new \InvalidArgumentException("Unknown question type: {$data['question_type']}"),
                };
            }
            $question->update([
                'topic_id' => (int) $data['topic'],
                'question_type' => $data['type'],
                'name' => $data['name'],
                'total_points' => $data['points']
            ]);
        });
    }

    public static function createFakeData(array $data, int $user_id)
        {
            if ($data['type'] == 'ranking' || $data['type'] == 'matching'){
            $items = request()->input('items', []);
            $totalPoints = 0;

            foreach ($items as $item) {
                if (isset($item['points']) && is_numeric($item['points'])) {
                    $totalPoints += (int) $item['points'];
                }
            }
            $data['points'] = $totalPoints;
        }
            $question_data = [
                'topic_id' => (int) $data['topic'],
                'question_type' => $data['type'],
                'name' => $data['name'],
                'total_points' => $data['points'],
                'created_by' => $user_id,
                'updated_by' => $user_id,
            ];   
        
            DB::transaction(function () use ($question_data, $data) {
                $question = Question::create($question_data);
                $question_type_service = new QuestionTypeService();
                if ($question_data['question_type'] == 'coding'){
                            $coding_question_data = [
                                    'instruction' => $data['instruction'],
                                    'syntax_points' => $data['syntax_points'],
                                    'runtime_points' => $data['runtime_points'],
                                    'test_case_points' => $data['test_case_points'],
                                ];
                            $coding_question_language_data = json_decode($data['supported_languages'], true);
                            $question_type_service->storeCoding(
                                    $question, $question_data, $coding_question_data, $coding_question_language_data
                            );
                } else {
                    $question_type_data = [
                    'items' => $data['items'] ?? [],
                    'solution' => $data['solution'] ?? '',
                    'points' => $data['points'] ?? ''
                    ]; 
                    match ($question_data['question_type']) {
                        'multiple_choice' => $question_type_service->storeMultipleChoice($question, $question_type_data),
                        'true_or_false' => $question_type_service->storeTrueOrFalse($question, $question_type_data),
                        'identification' => $question_type_service->storeIdentification($question, $question_type_data),
                        'ranking' => $question_type_service->storeRanking($question, $question_type_data),
                        'matching' => $question_type_service->storeMatching($question, $question_type_data),
                        default => throw new \InvalidArgumentException("Unknown question type: {$question_data['question_type']}"),
                    };
                }
            });
        }
}
