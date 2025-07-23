<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Topic;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TopicController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function index(){
        $topics = $this->userService->gettopicsForUser(auth()->user())->paginate(10);
        $topics->load('subject', 'questions');
        $header = ['ID', 'Subject', 'Name', 'Question Count', 'Date Created'];
        $rows = $topics->map(function ($topic) {
            return [
                'id' => $topic->id,
                'subject' => $topic->subject->name,
                'name' => $topic->name,
                'question count' => $topic->questions->count(),
                'Date Created' => Carbon::parse($topic->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows,
            'topics' => $topics
        ];

        return view('topics/index', $data);
    }

    public function show(Topic $topic){
        $topic->load( 'subject.course');
        $topic->load('questions');

        $header = ['ID', 'Name', 'Type', 'Date Created'];
        $rows = $topic->questions->map(function ($question) {
            return [
                'id' => $question->id,
                'name' => $question->name,
                'type' => $question->question_type->name,
                'Date Created' => Carbon::parse($question->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows,
            'topic'=>$topic
        ];

        return view('topics/show', $data);
    }
    public function create(){
        $subjects = $this->userService->getSubjectsForUser(auth()->user())->pluck('name', 'id');

        return view('topics/create', ['subjects' => $subjects]);
    }

    public function store(){
        $validator = Validator::make(request()->post(), [
            'name'    => ['required'],
            'subject' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        if ($validator->fails()) {
            $subjects = $this->userService->getSubjectsForUser(auth()->user())->pluck('name', 'id');
            return response()->view('topics.create', [
                'errors' => $validator->errors(),
                'subjects' => $subjects,
                'old' => request()->all()]);
        }

        Topic::create([
            'name' => request('name'),
            'subject_id' => request('subject'),
        ]);

        return response('', 200)->header('HX-Redirect', route('topics.index'));
    }
    public function edit(Topic $topic){
        $subjects = Subject::whereIn('course_id', $topic->subject->course()->get()->pluck('id'))->get()->pluck('name', 'id');

        $data = [
            'topic' => $topic, 
            'subjects' => $subjects
        ];
    
        return view('topics/edit', $data);
    }

    public function update(Topic $topic){
        $this->authorize('update', $topic);


        request()->validate([
            'name'    => ['required'],
            'subject'     => ['required', 'integer'],
        ]);

        $topic->update([
            'name' => request('name'),
            'subject_id' => request('subject'),
        ]);

        return redirect()->route('topics.show', $topic);
    }
    public function destroy(Topic $topic){

        $this->authorize('delete', $topic);

        $topic->delete();

        return redirect('/topics');

    }
}
