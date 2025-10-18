<?php

namespace App\Services;
use App\Models\Exam;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Models\StudentPaper;
use App\Models\User;
use DB;
use Storage;
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
            return false;
        }

        if (!$exam->is_published){
            return false;
        }
                
        $now = now();
        if ($exam->examination_date && $exam->expiration_date) {
            if ($now >= $exam->examination_date && $now <= $exam->expiration_date) {
                // return false;
                return true; // dapat pwede ka magtake ng exam
            }
        }



        $student_attempts_left = $this->getAttemptsLeft($exam, $user);
        if($student_attempts_left == 0){
            return false;
        }
            
        return true;
    }

    public function getAttemptsLeft(Exam $exam, User $user){
        $get_student_paper_count = $user->exams()->where('exam_id', $exam->id)->withCount('studentPapers')->first()->student_papers_count;
        $attempt_left = max(0, $exam->retakes - $get_student_paper_count);
        return $attempt_left;
    }

    public function checkUnsubmittedExamPaper(Exam $exam, User $user){
        $exam_paper = $user->studentPapers()->where(['exam_id' => $exam->id, 'status' => 'in_progress'])->first() ?? false;
        if (!$exam_paper){
            $exam_paper = $this->generateExamPaper($exam, $user);
        } else {
            $exam_paper['last_seen_at'] = now();
        }
        return $exam_paper;

    }
    public function checkBooleanUnsubmittedExamPaper(Exam $exam, User $user){
        $exam_paper = $user->studentPapers()->where(['exam_id' => $exam->id, 'status' => 'in_progress'])->first() ?? false;
        if (!$exam_paper){
            return false;
        } else {
            return true;
        }
    }

    public function generateExamPaper(Exam $exam, User $user){
        $exam->load('questions');
        // Prepare student paper information
        $shuffled_ids = self::applyKnuthShuffleToExam($exam);
        $question_count = count($shuffled_ids);

        // Create student paper
        $student_paper = StudentPaper::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'questions_order' => json_encode($shuffled_ids),
            'question_count' => $question_count,
            'current_position' => 0
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
        $student_paper->questions_in_array = $questions;

        return $student_paper;
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
        // $questions = collect($shuffled_ids)->map(fn($id) => [
        //     'id' => $questions[$id]->id,
        //     'question_type' => $questions[$id]->question_type->value
        // ])->pluck('question_type' ,'id')->toArray();
        return $questions;
    }

    public function getCurrentQuestion(StudentPaper $student_paper){
        if ($student_paper->current_position >= $student_paper->question_count){
                $student_paper->update(['current_position' => 0]);
        }
        $questions = json_decode($student_paper->questions_order);
        $question = Question::find($questions[$student_paper->current_position]);

        // check if there are answers regarding this question and send it instead of new question
        $student_answer = $student_paper->studentAnswers()->where('question_id', $question->id)->first();
        $question_type_data = $this->questionService->getQuestionTypeShow($question);
        
        $question_type_data = self::filterQuestionTypeData($question, $question_type_data);
        
        $filtered_question_type['choices'] = $question_type_data; 
        if($student_answer->is_answered){
            $question_type_answer = $this->getAnswerType($student_answer, $question);
            if ($question_type_answer != null){
                
                if ($question->question_type->value == 'coding'){
                    $filtered_question_type['choices']['language_codes'][$question_type_answer['language']]['initial_solution'] = $question_type_answer['code'];
                } else {
                    $filtered_question_type['student_answer'] = $question_type_answer;
                }

            } 
        }

        $question_data = [
            'question' => $question,
            'question_type_data' => $filtered_question_type
        ];
        return $question_data;
    }

    public static function filterQuestionTypeData(Question $question, $question_type){
        switch ($question->question_type->value){
            case('multiple_choice') :
                unset($question_type['points']);
                foreach ($question_type as $item => &$data) {
                    unset($data['is_solution']);
                }                
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
                $rightItems = array_column($question_type, 'right');
                shuffle($rightItems); 

                foreach ($question_type as $index => &$pair) {
                    $pair['right'] = $rightItems[$index];
                }
                
                shuffle($question_type);
                break;
            case('coding') : 
                foreach ($question_type['language_codes'] as $item => &$data) {
                    unset($data['complete_solution']);
                }
                $question_type['languages'] = $question_type['languages']->mapWithKeys(function ($item) {
                    return [$item => $item];
                });
                unset($question_type['syntax_points']);
                unset($question_type['runtime_points']);
                unset($question_type['test_case_points']);
                break;
        }

        return $question_type;
    }

    public function getAnswerType(StudentAnswer $student_answer, Question $question){
        match ($question->question_type->value){
            'multiple_choice' => $student_answer->load('multipleChoiceAnswer'),
            'true_or_false' => $student_answer->load('trueOrFalseAnswer'),
            'identification' => $student_answer->load('identificationAnswer'),
            'ranking' => $student_answer->load('rankingAnswers'),
            'matching' => $student_answer->load('matchingAnswers'),
            'coding' => $student_answer->load('codingAnswer'),
            default => throw new \InvalidArgumentException("Unknown question type: {$question->question_type->value}"),
        };

        if ($question->question_type->value == 'coding'){
            $coding_answer = $student_answer->codingAnswer ?? false;

            if (!$coding_answer) {
                return null;
            }

            $language = $coding_answer->answer_language;
            $code = Storage::get($coding_answer->answer_file_path);
            
            return ['language' => $language, 'code' => $code];
        } else {
            $suffix = in_array($question->question_type->value, ['ranking', 'matching']) ? 'Answers' : 'Answer';
            $question_type_answer = Str::camel($question->question_type->value) . $suffix;

            return $student_answer->$question_type_answer;
        }
    }

    public function orderedQuestions(StudentPaper $student_paper, Exam $exam)
    {
        $order = json_decode($student_paper->questions_order, true);
        $questionsMap = $exam->questions->whereIn('id', $order)->keyBy('id');

        return collect($order)->map(function ($id, $index) use ($questionsMap) {
            $question = $questionsMap->get($id);
            if ($question) {
                $question->order_index = $index;
                return $question;
            }
            return null;
        })->filter()->values();
    }

}

