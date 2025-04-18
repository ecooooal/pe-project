<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class QuestionController extends Controller
{
    public function getQuestionsForUser()
    {   
        $user = auth()->user();
        $user_courses = $user->getCourseIds();
        $subjectIds = Subject::whereIn('course_id', $user_courses)->get()->pluck('id');
        $topicIds = Topic::whereIn('subject_id', $subjectIds)->get()->pluck('id');;
        return Question::wherein('topic_id', $topicIds)->get();
    }

    public function index(){
        $questions = $this->getQuestionsForUser();
        $header = ['ID', 'Name', 'Subject', 'Topic', 'Type', 'Author', 'Date Created'];
        $rows = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'name' => $question->name,
                'subject' => $question->topic->subject->name,
                'topic' => $question->topic,
                'type' => $question->question_type,
                'author' => $question->created_by,
                'Date Created' => Carbon::parse($question->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows
        ];

        return view('questions/index', $data);
    }

    public function show(Question $question){
        return view('questions/show', ['question' => $question]);
    }
    public function create(){
        $topics = Topic::all()->pluck('name', 'id');
        $question_types = [
            '' => 'Select A Question Type',
            'multiple_choice' => 'Multiple Choice',
            'true_or_false'=> 'True or False',
            'identification' => 'Identification',
            'ranking_ordering_process' => 'Ranking/Ordering/Process',
            'matching' => 'Matching Items',
            'coding' => 'Coding'
        ];

        $data =[
            'topics' => $topics,
            'question_types' => $question_types
        ];

        return view('questions/create', $data);
    }

    public function store(){
        $first_validator = Validator::make(request()->all(), [
            'topic' => ['required'],
            'type' => ['required'],
            'name' => ['required', 'string'],
            'points' => ['required', 'integer', 'min:1'],
        ]);

        if ($first_validator->fails()) {
            session()->flash('first_validation', true);
            return redirect()->route('questions.create')
                ->withErrors($first_validator)
                ->withInput();
        }

        $second_validator = Validator::make(request()->all(), [
            'items' => ['required', 'array', 'min:2'],
            'items.*' => ['required', 'string', 'min:1'],
            'solution' => ['required', 'string'], 
        ], [
            'items.*.required' => 'This field is required.',
        ]);

        $question_type = request('type');

        if ($second_validator->fails()) {
            return redirect()->route('question.types', ['type' => $question_type])
                ->withErrors($second_validator)
                ->withInput();
        }

        \Log::info('data post', request()->post());
        dd('error');
        Question::create([
            'name' => request('name'),
            'subject_id' => request('subject'),
        ]);

        return redirect('/questions');
    }
    public function edit(Question $question){
        $subjects = Subject::whereIn('course_id', $question->subject->course()->get()->pluck('id'))->get()->pluck('name', 'id');

        $data = [
            'question' => $question, 
            'subjects' => $subjects
        ];
    
        return view('questions/edit', $data);
    }

    public function update(Question $question){
        $this->authorize('update', $question);


        request()->validate([
            'name'    => ['required'],
            'subject'     => ['required', 'integer'],
        ]);

        $question->update([
            'name' => request('name'),
            'subject_id' => request('subject'),
        ]);

        return redirect()->route('questions.show', $question);
    }
    public function destroy(Question $question){

        $this->authorize('delete', $question);

        $question->delete();

        return redirect('/questions');

    }
}
