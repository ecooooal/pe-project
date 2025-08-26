<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

    public function systemReportShow(){
        $exam = Exam::count();

        return view('components/graphs/homepage-system', ['exam' =>$exam]);
    }

    public function examReportShow(){
        $exam_dashboard_data = Http::timeout(30)->get('http://fastapi:80/api/dashboard/load-exam')->json();
        $examination_dates = collect($exam_dashboard_data['examination_dates'])
            ->map(function ($row) {
                return [
                    'id'   => $row[0],
                    'name' => $row[1],
                    'date' => $row[2],
                ];
            })->values()->toArray();

        $grouped = collect($examination_dates)    
            ->groupBy('date')
            ->map(function ($items) {
                return [
                    'names' => $items->pluck('name')->all(),
                    'count' => $items->count(),
                ];
            });

        $question_exams = collect($exam_dashboard_data['question_exams'])->map(function ($row) {
            try {
                $formatted_date = Carbon::parse($row[3])->format('F j, Y');
            } catch (\Exception $e) {
                $formatted_date = 'No Examination Date';
            }

            return [
                'id'   => $row[0],
                'name' => $row[1],
                'is_published' => $row[2],
                'examination_date' => $formatted_date,
                'question_count' => $row[4]
            ];
        })->values()->toJson();

        $exam_courses = collect($exam_dashboard_data['exam_courses'])->map(function ($row) {
            return [
                'id'   => $row[0],
                'name' => $row[1],
                'abbreviation' => $row[2],
                'exam_count' => $row[3]
            ];
        })->values()->toJson();
        $exam = [
            'count' => $exam_dashboard_data['exam_count'],
            'published_count' => $exam_dashboard_data['published_count'],
            'unpublished_count' => $exam_dashboard_data['unpublished_count'],
            'examination_dates' => $grouped,
            'question_exams' => $question_exams,
            'exam_courses' => $exam_courses,
            'exams' => $exam_dashboard_data
        ];
        
        return view('components/graphs/homepage-exam', $exam);
    }
    public function courseReportShow(){
        $user = auth()->user();
        $user_courses = Course::find($user->getCourseIds());
        $courses_abbv = $user_courses->mapWithKeys(function ($course) {
            return [$course->id => $course->abbreviation];
        });
        $exam = Exam::count();

        return view('components/graphs/homepage-course', ['exam' =>$exam, 'courses' => $courses_abbv]);
    }
}
