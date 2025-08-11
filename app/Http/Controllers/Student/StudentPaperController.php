<?php

namespace App\Http\Controllers\Student;

use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\StudentPaper;
use App\Services\ExamService;
use App\Services\ExamTakingService;
use Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

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

        $exam_id = $exam->id;
        $attempt_count = ExamRecord::whereHas('studentPaper', function($query) use ($exam_id, $student_paper) {
            $query->where('exam_id', $exam_id)
                ->where('user_id', $student_paper->user_id);
        })->count();

        session(['current_attempt' => $attempt_count + 1]);

        $data = [
            'student_paper' => $student_paper,  
            'exam' => $exam,
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
