<?php

namespace App\Http\Controllers\Student;

use App\Models\Exam;
use App\Models\StudentPaper;
use App\Services\ExamService;
use App\Services\ExamTakingService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StudentPaperController extends Controller
{
    protected $examTakingService;

    public function __construct(ExamTakingService $examTakingService)
    {
        $this->examTakingService = $examTakingService;
    }

    public function takeExam(Exam $exam)
    {
        $user = auth()->user();

        // validate User if allowed to take exam
        $can_take_exam = $this->examTakingService->validateExamAccess($exam, $user);
        if (!$can_take_exam){
            return redirect('/student');
        }

        // Check if the user has an existing exam paper that's not expired or submitted
        $student_paper = $this->examTakingService->checkUnsubmittedExamPaper($exam, $user);
        $question_count = count(json_decode($student_paper->questions_order));

        $data = [
            'student_paper' => $student_paper,
            'question_count' => $question_count,
            'exam' => $exam
        ];

        return view( 'students/papers/layout-take-exam', $data);
    }

    public function show(StudentPaper $student_paper){
        if ($student_paper->current_position < 0){
            $student_paper->update(['current_position' => 0]);
        }
        $data = $this->examTakingService->getCurrentQuestion($student_paper);
        $data['student_paper'] = $student_paper;
        return view( 'students/papers/show', $data);
    }

    public function loadQuestionLinks(Exam $exam, StudentPaper $student_paper){
        $questions = $this->examTakingService->orderedQuestions($student_paper, $exam);
        return view('students/papers/question-links', ['questions_in_array' => $questions, 'student_paper' => $student_paper]);
    }
}
