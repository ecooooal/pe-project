<?php

namespace App\Services;
use App\Models\Course;
use App\Models\StudentAnswer;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Question;
use Illuminate\Support\Facades\Http;
use Storage;
use Str;
class AnswerService
{
    protected $result = [];
    protected $total_points = 0;
    protected $is_correct = false;
    public function storeMultipleChoice(StudentAnswer $student_answer, $answer, $question_type){
        foreach($question_type['choices'] as $choice){
            if ($choice['choice_key'] === $answer) {
                if ($choice['is_solution']){
                    $this->total_points += $question_type['points'];
                    $this->is_correct = true;
                }
            }
        }
        $student_answer->multipleChoiceAnswer()->UpdateorCreate(['answer' => $answer]);
        
        return ['total_points' => $this->total_points, 'is_correct' => $this->is_correct];
    }
    public function storeTrueOrFalse(StudentAnswer $student_answer, $answer, $question_type, $question){
        if($answer === $question_type['solution']){
            $this->total_points = $question['total_points'];
            $this ->is_correct = true;
        }         
        $student_answer->trueOrFalseAnswer()->UpdateorCreate(['answer' => $answer]);
        
        return ['total_points' => $this->total_points, 'is_correct' => $this->is_correct];
    }
    public function storeIdentification(StudentAnswer $student_answer, $answer, $question_type, $question){
        if($answer === $question_type['solution']){
            $this->total_points = $question['total_points'];
            $this ->is_correct = true;
        } 
        $student_answer->identificationAnswer()->UpdateorCreate(['answer' => $answer]);
        
        return ['total_points' => $this->total_points, 'is_correct' => $this->is_correct];
    }
    public function storeRanking(StudentAnswer $student_answer, $answer, $question_type){
        $current_total_points = 0;
        foreach ($answer as $index => $row_answer) {
            $matched = false;

            foreach ($question_type as $correct) {
                if ($row_answer === $correct['solution'] && $index+1 === $correct['order']) {
                    $results[$index] = [
                        'answer_order' => $correct['order'],
                        'answer' => $row_answer,
                        'item_points' => $correct['item_points'],
                    ];
                    $this->total_points += $correct['item_points'];
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $results[$index] = [
                    'answer_order' => $index + 1, // fallback if order unknown
                    'answer' => $row_answer,
                    'item_points' => 0,
                ];
            }
        }
        $student_answer->rankingAnswers()->delete();
        foreach($results as $result => $data){
            $student_answer->rankingAnswers()->UpdateorCreate([
                'answer_order' => $data['answer_order'],
                'answer' => $data['answer'],
                'answer_points' => $data['item_points']
            ]);
            $current_total_points += $data['item_points'];
        }
        
        return ['total_points' => $this->total_points, 'is_correct' => $this->is_correct];
    }
    public function storeMatching(StudentAnswer $student_answer, $answer, $question_type, $question){
        $current_total_points = 0;
        foreach ($answer as $index => $student) {
            $matched = false;
            foreach ($question_type as $correct) {
                if (
                    $student['left'] === $correct['left'] &&
                    $student['right'] === $correct['right']
                ) {
                    $results[$index] = [
                        'left' => $student['left'],
                        'right' => $student['right'],
                        'item_points' => $correct['item_points'],
                    ];
                    $this->total_points += $correct['item_points'];
                    $matched = true;
                    break;
                }
            }
            
            if (!$matched) {
                $results[$index] = [
                    'left' => $student['left'],
                    'right' => $student['right'],
                    'item_points' => 0,
                ];
            }
        }
        $student_answer->matchingAnswers()->delete();
        foreach($results as $result => $data){
            $student_answer->matchingAnswers()->UpdateorCreate([
                'first_item_answer' => $data['left'],
                'second_item_answer' => $data['right'],
                'answer_points' => $data['item_points']
            ]);
            $current_total_points += $data['item_points'];
        }
        $this->is_correct = $question['total_points'] === $current_total_points;
        
        return ['total_points' => $this->total_points, 'is_correct' => $this->is_correct];
    }
    public function storeCoding(Question $question, array $question_data, $coding_question_data, array $coding_question_language_data){
        // get language and code
        // save code in directory
        // 
    }

    private static function getClassName(string $code, string $language): string
    {
        $patterns = [
            'java' => '/\bclass\s+(\w+)/',
            'c++' => '/\b(?:class|struct)\s+(\w+)/',
            'python' => '/^[ \t]*class\s+(\w+)/m',
        ];

        $pattern = $patterns[strtolower($language)] ?? '/\bclass\s+(\w+)/';
        $class_name = 'tmp';

        if (preg_match($pattern, $code, $matches)) {
            $class_name = $matches[1];
        }

        return $class_name;
    }

    private static function getExtension(string $language): string
    {
        return match (strtolower($language)) {
            'java' => 'java',
            'python' => 'py',
            'c++' => 'cpp',
            default => 'txt',
        };
    }
}
