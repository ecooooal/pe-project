<?php

namespace App\Http\Controllers\Student;

use App\Models\ExamRecord;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index(){
        $user = auth()->user();
        $enrolled_exams = $user->exams ?? [];
        $exam_records = $user->examRecords()
            ->with(['studentPaper:id,exam_id,id', 'studentPaper.exam:id,uuid,name,max_score,id']) // adjust fields
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get();
        $enrolled_exams->load('courses');

        $data = [
            'enrolled_exams' => $enrolled_exams,
            'exam_records' => $exam_records,
            'user' => $user
        ];

        return view('students/student-home', $data);

    }
}
