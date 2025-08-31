<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subject;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index(){
        $course_courses = Course::all();
        $header = ['ID', 'Name', 'Date Created'];
        $rows = $course_courses->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'Date Created' => Carbon::parse($course->created_at)->format('m/d/Y')
            ];  
        });

        $data = [
            'headers' => $header,
            'rows' => $rows,
            'courses' => $course_courses
        ];

        return view('courses/index', $data);
    }

    public function show(Course $course){
        $header = ['ID', 'Name', 'Year Level', 'Date Created'];
        $subjects = Subject::with(['course'])
            ->where('course_id', $course->id)
            ->Paginate(10);

        $rows = $subjects->map(fn($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
                'type' => $subject->year_level,
                'Date Created' => Carbon::parse($subject->created_at)->format('m/d/Y')
        ]);

        $data = [
            'headers' => $header,
            'rows' => $rows,
            'course'=>$course,
            'subjects' => $subjects
        ];
        return view('courses/show', $data);
    }

    public function create(){
        return view('courses/create');
    }

    public function store(){
        $data = request()->post();
        $data['abbreviation'] = strtoupper($data['abbreviation']);

        $validator = Validator::make($data, [
            'name'    => ['required', 'unique:courses,name'],
            'abbreviation' => ['required', 'unique:courses,abbreviation']
        ]);

        if ($validator->fails()) {
            return response()->view('courses.create', [
                'errors' => $validator->errors(),
                'old' => $data]);
        }

        $course = Course::create([
            'name' => $data['name'],
            'abbreviation' => $data['abbreviation']
        ]);

        session()->flash('toast', json_encode([
            'status' => 'Created!',
            'message' => 'Course: ' . $course->name,
            'type' => 'success'
        ]));

        return response('', 200)->header('HX-Redirect', route('courses.index'));
    }

    public function edit(Course $course){
        $data = [
            'course' => $course, 
        ];
    
        return view('courses/edit', $data);
    }

    public function update(Course $course){
        $data = request()->all();
        $data['abbreviation'] = strtoupper($data['abbreviation']);
        request()->merge($data);

        request()->validate([
            'name' => [
                'required',
                Rule::unique('courses', 'name')->ignore($course->id),
            ],
            'abbreviation' => [
                'required',
                Rule::unique('courses', 'abbreviation')->ignore($course->id),
            ],
        ]);


        $course->update([
            'name' => $data['name'],
            'abbreviation' => $data['abbreviation']   
        ]);

        session()->flash('toast', json_encode([
            'status' => 'Updated!',
            'message' => 'Course: ' . $course->name,
            'type' => 'info'
        ]));

        return redirect()->route('courses.show', $course);
    }
    public function destroy(Course $course){

        $this->authorize('delete', $course);

        if ($course->subjects()->exists()) {
            return back()->with('error', 'You cannot delete a course that has subjects.');
        }
        session()->flash('toast', json_encode([
            'status' => 'Destroyed!',
            'message' => 'Course: ' . $course->name,
            'type' => 'warning'
        ]));

        $course->delete();

        return redirect('/courses');

    }
}
