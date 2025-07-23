<?php

namespace App\Http\Controllers\Student;

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

        $data = [
            'enrolled_exams' => $enrolled_exams,
            'user' => $user
        ];

        return view('students/student-home', $data);

    }
}
