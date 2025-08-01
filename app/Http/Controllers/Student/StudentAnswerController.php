<?php

namespace App\Http\Controllers\Student;

use App\Models\Exam;
use App\Models\Question;
use App\Factories\StudentAnswerFactory;
use App\Models\StudentPaper;
use App\Services\ExamTakingService;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentAnswerController extends Controller
{
    protected $examTakingService;

    public function __construct(ExamTakingService $examTakingService)
    {
        $this->examTakingService = $examTakingService;
    }

    public function update(StudentPaper $student_paper, Question $question)
    {
        // authorize using studentpaper, question id
        $student_answer = $student_paper->studentAnswers()->where(['student_paper_id' => $student_paper->id, 'question_id' => $question->id])->first();
        if ($student_answer == null){
            dd('yes');
        }   
        // update and check student answer here
        StudentAnswerFactory::update($student_answer, request()->post());

        $action = request()->input('action');
        // increment current position
        match ($action) {
            'back' => $student_paper->decrement('current_position'),
            'next' => $student_paper->increment('current_position'),
            default => dd($student_paper),
        };

        $isLastQuestion = $student_paper->current_position >= $student_paper->question_count;

        if ($isLastQuestion){
            return redirect()->route('exam_records.store', $student_paper);
        } else {
            $data = $this->examTakingService->getCurrentQuestion($student_paper);
            $data['student_paper'] = $student_paper;
            $data['isLastQuestion'] = $isLastQuestion;
            return view( 'students/papers/show', $data);
        }
    }
}
