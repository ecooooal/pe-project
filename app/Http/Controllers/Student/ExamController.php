<?php

namespace App\Http\Controllers\Student;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Services\ExamService;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
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

    public function index(){

        return;

    }

    public function show(Exam $exam){
        return view(view: 'students/exams/show');
    }

    public function showExamRecord(Exam $exam){
        return view(view: 'students/records/show');
    }
    public function create(){
        return;

    }

    public function store(){
    
        return;
    }

    public function edit(Exam $exam){
        return;

    }

    public function update(Exam $exam){
        return;

    }

    public function destroy(Exam $exam){
        return;
    }
    public function getExamPapers(Exam $exam){
        return view(view: 'students/exams/get-exam-papers');
    }
    public function getExamOverview(Exam $exam){
        return view(view: 'students/exams/get-exam-overview');
    }
}