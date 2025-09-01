<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $header = ['ID', 'Name', 'Type', 'Date Created'];
        $questions = Question::with(['topic'])
            ->where('topic_id', $topic->id)
            ->Paginate(5);

        $rows = $questions->map(fn($question) => [
                'id' => $question->id,
                'name' => $question->name,
                'type' => $question->question_type->name,
                'Date Created' => Carbon::parse($question->created_at)->format('m/d/Y')
        ]);
        $data = [
            'headers' => $header,
            'rows' => $rows,
            'topic'=>$topic,
            'questions' => $questions
        ];

        return view('topics/show', $data);
    }
    public function create(){
        $subjects = $this->userService->getSubjectsForUser(auth()->user())->pluck('name', 'id');

        return view('topics/create', ['subjects' => $subjects]);
    }

    public function store(){
        $validator = Validator::make(request()->post(), [
            'name'    => ['required', 'unique:topics,name'],
            'subject' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        if ($validator->fails()) {
            $subjects = $this->userService->getSubjectsForUser(auth()->user())->pluck('name', 'id');
            return response()->view('topics.create', [
                'errors' => $validator->errors(),
                'subjects' => $subjects,
                'old' => request()->all()]);
        }

        $topic = Topic::create([
            'name' => request('name'),
            'subject_id' => request('subject'),
        ]);

        session()->flash('toast', json_encode([
            'status' => 'Created!',
            'message' => 'Topic: ' . $topic->name,
            'type' => 'success'
        ]));

        return response('', 200)->header('HX-Redirect', route('topics.index'));
    }
    public function edit(Topic $topic)
    {
        // Get the course IDs that the current topic's subject belongs to
        $courseIds = $topic->subject->courses->pluck('id');

        // Get all subjects linked to those courses (via pivot table)
        $subjects = Subject::whereHas('courses', function ($query) use ($courseIds) {
            $query->whereIn('courses.id', $courseIds);
        })->pluck('name', 'id');

        return view('topics.edit', [
            'topic' => $topic,
            'subjects' => $subjects
        ]);
    }


    public function update(Topic $topic){
        $this->authorize('update', $topic);


        request()->validate([
            'name'    => ['required', Rule::unique('topics', 'name')->ignore($topic->id)],
            'subject'     => ['required', 'integer'],
        ]);

        $topic->update([
            'name' => request('name'),
            'subject_id' => request('subject'),
        ]);
    
        session()->flash('toast', json_encode([
            'status' => 'Updated!',
            'message' => 'Topic: ' . $topic->name,
            'type' => 'info'
        ]));

        return redirect()->route('topics.show', $topic);
    }
    public function destroy(Topic $topic){

        $this->authorize('delete', $topic);

        if ($topic->questions()->exists()) {
            return back()->with('error', 'You cannot delete a topic that has questions.');
        }
        session()->flash('toast', json_encode([
            'status' => 'Destroyed!',
            'message' => 'Topic: ' . $topic->name,
            'type' => 'warning'
        ]));

        $topic->delete();

        return redirect('/topics');

    }
}
