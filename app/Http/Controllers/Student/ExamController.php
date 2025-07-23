<?php

namespace App\Http\Controllers\Student;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAccessCode;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamService;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Str;

class ExamController extends Controller
{
    protected $userService;
    protected $examService;

    public function __construct(UserService $userService, ExamService $examService)
    {
        $this->userService = $userService;
        $this->examService = $examService;
    }

    public function show(Exam $exam){
        $exam->load('questions');
        $questions = $exam->questions->mapWithKeys(function ($question) {
            return [$question->id => $question->getTypeModel()];
        });
        return view( 'students/exams/show', ['exam'=> $exam, 'questions' => $questions]);
    }

    public function showExamRecord(Exam $exam){
        return view(view: 'students/records/show');
    }

    public function store(User $user){
        $access_code = request('access-code');
        
        try {
        $exam_access_code = ExamAccessCode::where('access_code', $access_code)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return view('components/student/access-code-form', [
            'errors' => ['access-code' => ['Code not found.']],
            'old' => ['access-code' => request()->input('access-code')]
            ]);
        }

        $enrolled = $this->examService->enrollAccessCode($user, $exam_access_code);
        if (!$enrolled){
            return view('components/student/access-code-form', [
            'errors' => ['access-code' => ['Already enrolled in this Exam.']],
            'old' => ['access-code' => request()->input('access-code')]
            ]);
        }

        return response('', 200)->header('HX-Refresh', 'true');
    }

    public function getExamPapers(Exam $exam)
    {
        return view(view: 'students/exams/get-exam-papers');
    }
    public function getExamOverview(Exam $exam)
    {
        $exam->load('course');
        return view('students/exams/get-exam-overview', ['exam' => $exam]);
    }
}