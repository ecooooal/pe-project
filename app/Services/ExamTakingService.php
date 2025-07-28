<?php

namespace App\Services;
use App\Models\Exam;
use App\Models\ExamAccessCode;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Models\StudentPaper;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Str;
class ExamTakingService
{
    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }
    public function validateExamAccess(Exam $exam, User $user){
        $isEnrolled = $user->exams()->where('exam_id', $exam->id)->exists();
        if (!$isEnrolled){
            dd('not enrolled in this exam');
        }
        // check if exam is published
        if (!$exam->is_published){
            dd('Not published');
        }
        // check examination date
        if ($exam->examination_date){
            dd('date');
        }
        return true;
    }

    public function checkUnsubmittedExamPaper(Exam $exam, User $user){
        $exam_paper = $user->studentPapers()->where(['exam_id' => $exam->id, 'status' => 'in_progress'])->first();
        if (!$exam_paper){
            return self::generateExamPaper($exam, $user);
        }

        $question_count = count(json_decode($exam_paper->questions_order));
        $questions = self::getShuffledQuestionsInfo($exam, json_decode($exam_paper->questions_order));

        $exam_paper_data = [
            'student_paper' => $exam_paper,
            'questions_in_array' => $questions,
            'question_count' => $question_count,
        ];
        return $exam_paper_data;
    }

    public static function generateExamPaper(Exam $exam, User $user){
        $exam->load('questions');
        // Prepare student paper information
        $shuffled_ids = self::applyKnuthShuffleToExam($exam);
        $question_count = count($shuffled_ids);

        // Create student paper
        $student_paper = StudentPaper::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'questions_order' => json_encode($shuffled_ids),
            'question_count' => $question_count
        ]);

        foreach($shuffled_ids as $id){
            StudentAnswer::create([
                'student_paper_id' => $student_paper->id,
                'question_id' => $id
            ]);
        }

        // Put data to be shown to user
        $questions = self::getShuffledQuestionsInfo($exam, $shuffled_ids);

        $exam->unsetRelation('questions');
        $exam_paper_data = [
            'student_paper' => $student_paper,
            'questions_in_array' => $questions,
            'question_count' => $question_count,
        ];
        return $exam_paper_data;
    }

    // algorithm for shuffling the question list
    public static function applyKnuthShuffleToExam(Exam $exam){
        $questions = $exam->questions->pluck('id')->toArray();

        // knuth shuffle
        for ($i = count($questions) - 1; $i > 0; $i--) {
            $picked_item = random_int(0, $i);
            [$questions[$i], $questions[$picked_item]] = [$questions[$picked_item], $questions[$i]];
        }
        return $questions;
    }

    public static function getShuffledQuestionsInfo(Exam $exam, array $shuffled_ids){
        $questions = $exam->questions->whereIn('id', $shuffled_ids)->keyBy('id');
        $questions = collect($shuffled_ids)->map(fn($id) => [
            'id' => $questions[$id]->id,
            'question_type' => $questions[$id]->question_type->value
        ])->pluck('question_type' ,'id')->toArray();
        return $questions;
    }

    public function getCurrentQuestion(StudentPaper $student_paper){
        if ($student_paper->current_position >= $student_paper->question_count){
                        $student_paper->update(['current_position' => 0]);

            dd('done');
        }
        $questions = json_decode($student_paper->questions_order);
        $question = Question::find($questions[$student_paper->current_position]);
        $question_type = $this->questionService->getQuestionTypeShow($question);
        
        $question_type = self::filterQuestionTypeData($question, $question_type);

        $question_data = [
            'question' => $question,
            'question_type_data' => $question_type
        ];
        return $question_data;
    }

    public static function filterQuestionTypeData(Question $question, $question_type){
        switch ($question->question_type->value){
            case('multiple_choice') :
                unset($question_type['points']);
                shuffle($question_type);
                break;
            case('true_or_false') :
                unset($question_type['solution']);
                unset($question_type['points']);
                break;
            case('identification') :
                unset($question_type['solution']);
                unset($question_type['points']);
                break;
            case('ranking') :
                foreach ($question_type as $item => &$data) {
                    unset($data['order']);
                    unset($data['item_points']);
                }
                shuffle($question_type);
                break;
            case('matching') :
                foreach ($question_type as $item => &$data) {
                    unset($data['item_points']);
                }
                shuffle($question_type);
                break;
            case('coding') : 
                foreach ($question_type['language_codes'] as $item => &$data) {
                    unset($data['complete_solution']);
                }
                unset($question_type['syntax_points']);
                unset($question_type['runtime_points']);
                unset($question_type['test_case_points']);
                break;
        }

        return $question_type;
    }

}

