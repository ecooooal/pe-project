<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Str;

class ExamController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(){
        $courseIds = $this->userService->getCoursesForUser(auth()->user())->pluck('id');
        $exams = Exam::whereIn('course_id', $courseIds)->get();

        $header = ['ID', 'Name', 'Course', 'Questions', 'Status', 'is Published', 'Date Created'];
        $rows = $exams->map(function ($exam) {
            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'course' => $exam->course->name,
                'questions' => $exam->questions->count(),
                'status' => $exam->questions()->sum('points') >= $exam->max_score ? 'Complete' : 'Incomplete',
                'is_published' => $exam->published,
                'examination date' => Carbon::parse($exam->examination_date)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows
        ];

        return view('exams/index', $data);

    }

    public function show(){
        return view('exams/show');

    }

    public function create(){
        $courses = Course::all()->pluck('name', 'id');
        return view('exams/create', ['courses' => $courses]);

    }

    public function store(){
        request()->validate([
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'max_score' => 'required|integer|gte:1',
            'duration' => 'nullable|integer|min:1',
            'retakes' => 'nullable|integer|min:1',
            'examination_date' => 'nullable|date|after:now',
        ], [
            'course_id' => 'The course field is required.',
            'max_score' => 'The max score field is required',
            'examination_date' => 'The examination date field must be a date after now.'
        ]);
        $accessCode = strtoupper(implode('-', str_split(Str::random(8), 4)));
        Exam::create([
            'name' => request('name'),
            'course_id' => request('course_id'),
            'access_code' => $accessCode,
            'max_score' => request('max_score'),
            'duration' => request('duration') ?? null,
            'retakes' => request('retakes') ?? null, 
            'examination_date' => request('examination_date'),
        ]);

        return redirect('/exams');
    }

    public function edit(){
        return view('exams/edit');

    }

    public function update(){
        
    }

    public function destroy(){
        
    }
    
}
