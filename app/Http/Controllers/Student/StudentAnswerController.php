<?php

namespace App\Http\Controllers\Student;

use App\Models\Exam;
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

    public function store(Exam $exam)
    {
        $user = auth()->user();
        // validate User if allowed to take exam
        $can_take_exam = $this->examTakingService->validateExamAccess($exam, $user);
        if (!$can_take_exam){
            return redirect('/student');
        }

        // Check if the user has an existing exam paper that's not expired or submitted
        $tables = DB::select('SHOW TABLES');
        // If there are any give the one that is not submitted yet but not expired
        // session(['current_question_index' => $userExamRecord->current_position]);


        // Generate the exam paper and let user take the exam
        $exam->load('questions');
        $exam['question_count'] = $exam->questions->count();

        // Create student paper
        $student_paper = $this->examTakingService->generateExampaper($exam, $user);
        $question_ids = json_decode($student_paper['questions_order']); // array of IDs

        // Fetch all the questions that match those IDs
        $questions = $exam->questions->whereIn('id', $question_ids)->keyBy('id');
        $exam['questions_in_array'] = collect($question_ids)->map(function ($id) use ($questions) {
            $q = $questions->get($id);
            return $q ? [
                'id' => $q->id,
                'question_type' => $q->question_type,
            ] : null;
        })->filter()->values()->toArray();
        $exam->unsetRelation('questions');

        $data = [
            'student_paper' => $student_paper,
            'exam' => $exam,
            'table'=> $tables
        ];

        return view( 'students/exams/layout-take-exam', $data); ;
    }
}
