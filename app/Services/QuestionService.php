<?php

namespace App\Services;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Question;
use Illuminate\Support\Facades\Http;
class QuestionService
{
    public function getQuestionTypeShow(Question $question){
        $question_type = $question->getTypeModel();
        switch ($question->question_type->value) {
            case 'multiple_choice':
                $choices = $question_type->map(function ($choice) {
                    return [
                        'choice_key' => $choice->choice_key,
                        'item' => $choice->item,
                        'is_correct' => $choice->is_correct,
                    ];
                })->toArray();
                return $choices;

            case 'true_or_false':
                $choices = [
                    'solution' => $question_type->solution,
                ];                
                return $choices;

            case 'identification':
                $choices = [
                    'solution' => $question_type->solution,
                ];                
                return $choices;

            case 'ranking':
                $choices = $question_type->map(function ($choice) {
                    return [
                        'order' => $choice->order,
                        'item' => $choice->item,
                    ];
                })->toArray();
                return $choices;

            case 'matching':
                // MatchingQuestion::create($data);
                break;

            default:
                throw new \Exception("Unknown question type: {$question['question_type']}");
        }
    }

    public static function validate(string $language, string $solution, string $test): array
    {
        switch ($language) {
            case 'java':
                try {
                    $response = Http::timeout(30)->post('http://java-api:8082/validate', [
                        'completeSolution' => $solution,
                        'testUnit' => $test
                    ]);
                    if ($response->successful()) {
                        $data = $response->json();
                        $hasFailures = false;

                        if (isset($data['testResults']) && is_array($data['testResults'])) {
                            foreach ($data['testResults'] as $testResult) {
                                if (isset($testResult['methods']) && is_array($testResult['methods'])) {
                                    foreach ($testResult['methods'] as $method) {
                                        if (isset($method['status']) && $method['status'] === 'FAILED') {
                                            $hasFailures = true;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }

                        $data['success'] = !$hasFailures;
                        return $data;
                    }
                    return ['success' => false, 'error' => 'API returned error'];
                } catch (\Exception $e) {
                    return ['success' => false, 'error' => 'API unreachable | API may not be available', 'exception' => $e->getMessage()];
                }

            default:
                return ['success' => false, 'error' => 'Unsupported language'];
        }
    }
}
