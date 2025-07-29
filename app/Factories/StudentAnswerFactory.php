<?php

namespace App\Factories;

use App\Models\Question;
use App\Models\StudentAnswer;
use App\Services\QuestionService;
use App\Services\QuestionTypeService;
use Illuminate\Support\Facades\DB;

class StudentAnswerFactory
{
    public static function update(StudentAnswer $student_answer, array $data)
    {
        // load question and question type
        $question_service = new QuestionService();
        $question = $student_answer->question;
        $question_type = $question_service->getQuestionTypeShow($question);
        $answer = $data['answer'] ?? null;
        $result = [];
        $total_points = 0;

        switch ($question->question_type->value){
            case('multiple_choice'):
                $result['answer'] = $answer;
                foreach($question_type['choices'] as $choice){
                    if ($choice['choice_key'] === $answer) {
                        if ($choice['is_solution']){
                            $total_points += $question_type['points'];
                        }
                    }
                }

                dd($question_type, $result['answer'], $total_points);
                break;
            case('true_or_false'):
                $result['answer'] = $answer;
                $total_points = $answer === $question_type['solution'] ? $question['total_points'] : 0;
                dd($question_type, $answer);
                break;
            case('identification'):
                $result['answer'] = $answer;
                $total_points = $answer === $question_type['solution'] ? $question['total_points'] : 0;
                dd($question_type, $answer, $result['answer'], $answer === $question_type['solution'], $total_points);
                break;
            case('ranking'):
                foreach ($answer as $index => $row_answer) {
                    $matched = false;

                    foreach ($question_type as $correct) {
                        if ($row_answer === $correct['solution'] && $index+1 === $correct['order']) {
                            $result[$index] = [
                                'answer_order' => $correct['order'],
                                'answer' => $row_answer,
                                'item_points' => $correct['item_points'],
                            ];
                            $total_points += $correct['item_points'];
                            $matched = true;
                            break;
                        }
                    }

                    if (!$matched) {
                        $result[$index] = [
                            'answer_order' => $index + 1, // fallback if order unknown
                            'answer' => $row_answer,
                            'item_points' => 0,
                        ];
                    }
                }

                dd($question_type, $answer, $result, $total_points);
                break;
            case('matching'):
                foreach ($answer as $index => $student) {
                    $matched = false;

                    foreach ($question_type as $correct) {
                        if (
                            $student['left'] === $correct['left'] &&
                            $student['right'] === $correct['right']
                        ) {
                            $result[$index] = [
                                'left' => $student['left'],
                                'right' => $student['right'],
                                'item_points' => $correct['item_points'],
                            ];
                            $total_points += $correct['item_points'];
                            $matched = true;
                            break;
                        }
                    }

                    if (!$matched) {
                        $result[$index] = [
                            'left' => $student['left'],
                            'right' => $student['right'],
                            'item_points' => 0,
                        ];
                    }
                }
                dd($question_type, $answer, $result, $total_points);
                break;
            case('coding'):
                dd($question_type, $answer);
                break;
        }
        
        
    }

}
