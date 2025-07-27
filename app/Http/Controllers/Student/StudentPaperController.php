<?php

namespace App\Http\Controllers\Student;

use App\Models\Exam;
use App\Models\StudentPaper;
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

    public function store(Exam $exam)
    {
        $user = auth()->user();

        // validate User if allowed to take exam
        $can_take_exam = $this->examTakingService->validateExamAccess($exam, $user);
        if (!$can_take_exam){
            return redirect('/student');
        }

        // Check if the user has an existing exam paper that's not expired or submitted
        $student_paper = $this->examTakingService->checkUnsubmittedExamPaper($exam, $user);

        $tables = DB::select("Table student_papers ");
        $session = session(['helo'=> 'hdsf']);
        $data = [
            'student_paper' => $student_paper,
            'table'=> $tables,
            'exam' => $exam,
            'see' => $session
        ];

        return view( 'students/exams/layout-take-exam', $data);
    }
}
