<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function facultyShow(){
        $course = $this->userService->getCoursesForUser(auth()->user());
        $exam = $this->userService->getExamsForUser(auth()->user())->count();
        $data = [
            'course' => $course,
            'exam' => $exam
        ];


        return view('faculty-home', $data);
    }

    public function examReportShow(){
        $exam = Exam::count();

        return view('components/graphs/homepage-exam', ['exam' =>$exam]);
    }
    public function courseReportShow(){
        $exam = Exam::count();

        return view('components/graphs/homepage-course', ['exam' =>$exam]);
    }
}
