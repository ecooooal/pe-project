<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class SubjectController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index(){
        $subject_courses = $this->userService->getSubjectsForUser(auth()->user())->paginate(10);
        $subject_courses->load('course');
        $header = ['ID', 'Course', 'Name',  'Year Level', 'Date Created'];
        $rows = $subject_courses->map(function ($subject) {
            return [
                'id' => $subject->id,
                'course' => $subject->course->abbreviation,
                'name' => $subject->name,
                'year_level' => $subject->year_level,
                'Date Created' => Carbon::parse($subject->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows,
            'subjects' => $subject_courses
        ];

        return view('subjects/index', $data);
    }

    public function show(Subject $subject){
        $header = ['ID', 'Topic', 'Name', 'Type', 'Date Created'];
        $questions = Question::with(['topic'])
            ->whereIn('topic_id', $subject->topics->pluck('id'))
            ->Paginate(5);

        $rows = $questions->map(fn($question) => [
            'id' => $question->id,
            'topic' => $question->topic->name,
            'name' => $question->name,
            'type' => $question->question_type->name,
            'Date Created' => Carbon::parse($question->created_at)->format('m/d/Y'),
        ]);
        $data = [
            'headers' => $header,
            'rows' => $rows,
            'subject'=>$subject,
            'questions' => $questions,
        ];

        return view('subjects/show', $data);
    }

    public function create(){
        $courses = Course::all()->pluck('name', 'id');

        return view('subjects/create', ['courses' => $courses]);
    }

    public function store(){
        $validator = Validator::make(request()->post(), [
            'name'    => ['required', 'unique:subjects,name'],
            'course'     => ['required', 'integer'],
            'year_level' => ['required', 'integer', 'min:1', 'max:4']
        ]);

        if ($validator->fails()) {
            $courses = Course::all()->pluck('name', 'id');
            return response()->view('subjects.create', [
                'errors' => $validator->errors(),
                'courses' => $courses,
                'old' => request()->all()]);
        }

        $subject = Subject::create([
            'name' => request('name'),
            'course_id' => request('course'),
            'year_level' => request('year_level'),
        ]);

        session()->flash('toast', json_encode([
            'status' => 'Created!',
            'message' => 'Subject: ' . $subject->name,
            'type' => 'success'
        ]));

        return response('', 200)->header('HX-Redirect', route('subjects.index'));
    }

    public function edit(Subject $subject){
        $courses = Course::all()->pluck('name', 'id');

        $data = [
            'subject' => $subject, 
            'courses' => $courses
        ];
    
        return view('subjects/edit', $data);
    }

    public function update(Subject $subject){
        request()->validate([
            'name'    => ['required', Rule::unique('subjects', 'name')->ignore($subject->id)],
            'year_level' => ['required', 'integer', 'min:1', 'max:4']
        ]); 
        $subject->update([
            'name' => request('name'),
            'year_level' => request('year_level'),
        ]);

        
        session()->flash('toast', json_encode([
            'status' => 'Updated!',
            'message' => 'Subject: ' . $subject->name,
            'type' => 'info'
        ]));

        return redirect()->route('subjects.show', $subject);
    }
    public function destroy(Subject $subject){

        $this->authorize('delete', $subject);

        if ($subject->topics()->exists()) {
            return back()->with('error', 'You cannot delete a subject that has topics.');
        }

        session()->flash('toast', json_encode([
            'status' => 'Destroyed!',
            'message' => 'Subject: ' . $subject->name,
            'type' => 'warning'
        ]));

        $subject->delete();

        return redirect('/subjects');

    }

    public function showQuestions(Subject $subject){
        $subject->load('topics.questions');
        $header = ['ID', 'Topic', 'Name', 'Type', 'Author', 'Date Created'];
        $rows = $subject->topics->flatMap(function ($topics) {
            return $topics->questions;
        })->map(fn($question) => [
            'id' => $question->id,
            'topic' => $question->topic->name,
            'name' => $question->name,
            'type' => $question->question_type->name,
            'author' => $question->author->getFullName(),
            'Date Created' => Carbon::parse($question->created_at)->format('m/d/Y')
        ]);
        $data = [
            'headers' => $header,
            'rows' => $rows,
            'subject'=>$subject
        ];
        return view('subjects/questions', $data);
    }
}
